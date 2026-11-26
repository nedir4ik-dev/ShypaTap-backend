<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminPaymentController extends Controller
{
    public function index(Request $request)
    {
        $clinicIds = in_array(strtolower((string)$request->user()->role), ['superadmin','super_admin'], true) ? \App\Models\Clinic::pluck('id') : $request->user()->staffClinics()->pluck('clinics.id');
        $payments = Payment::whereHas('appointment',fn($q)=>$q->whereIn('clinic_id',$clinicIds))->with([
            'appointment.patient:id,name,email',
            'appointment.doctor.user:id,name',
            'appointment.clinic:id,name',
            'histories.actor:id,name,role',
        ])->latest()->paginate(25);

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json($payments);
        }

        return view('admin.payments.index', compact('payments'));
    }

    public function show(Request $request, Payment $payment)
    {
        abort_unless($request->user()->belongsToClinic((int)$payment->appointment?->clinic_id),403);
        $payment->load(['appointment.patient', 'appointment.doctor.user', 'appointment.clinic', 'histories.actor']);
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['data' => $payment]);
        }

        return view('admin.payments.show', compact('payment'));
    }

    public function update(Request $request, Payment $payment)
    {
        abort_unless($request->user()->belongsToClinic((int)$payment->appointment?->clinic_id),403);
        $data = $request->validate([
            'status' => ['required', Rule::in(['pending', 'paid', 'refunded', 'cancelled'])],
            'payment_method' => ['sometimes', Rule::in(['cash'])],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($payment, $data, $request) {
            $locked = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();
            $oldStatus = $locked->status;
            $oldMethod = $locked->payment_method;
            $newStatus = $data['status'];
            $newMethod = $data['payment_method'] ?? $oldMethod;

            if ($newStatus === 'refunded') abort_unless($request->user()->hasPermission('payments.refund', (int)$locked->appointment?->clinic_id),403);
            if ($newStatus === 'paid') abort_unless($request->user()->hasPermission('payments.mark_paid', (int)$locked->appointment?->clinic_id),403);
            $allowedTransitions = [
                'pending' => ['paid', 'cancelled'],
                'paid' => ['refunded'],
                'refunded' => [],
                'cancelled' => [],
            ];
            if ($newStatus !== $oldStatus && !in_array($newStatus, $allowedTransitions[$oldStatus] ?? [], true)) {
                abort(422, 'Bu töleg ýagdaýyndan saýlanan ýagdaýa geçmek rugsat berilmeýär.');
            }

            $locked->update([
                'status' => $newStatus,
                'payment_method' => $newMethod,
                'paid_at' => $newStatus === 'paid' ? ($locked->paid_at ?? now()) : $locked->paid_at,
                'refunded_at' => $newStatus === 'refunded' ? ($locked->refunded_at ?? now()) : $locked->refunded_at,
            ]);

            $locked->histories()->create([
                'actor_user_id' => $request->user()->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'old_payment_method' => $oldMethod,
                'new_payment_method' => $newMethod,
                'note' => $data['note'] ?? null,
                'reason' => $data['note'] ?? null,
                'amount' => $locked->amount,
            ]);
        });

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['message' => 'Töleg ýagdaýy täzelendi.', 'data' => $payment->fresh()]);
        }

        return back()->with('success', 'Töleg ýagdaýy täzelendi.');
    }
}
