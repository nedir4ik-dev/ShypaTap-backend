<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    public function receptionistIndex(Request $request)
    {
        $user = $request->user();
        $clinicIds = in_array(strtolower((string)$user->role), ['superadmin','super_admin'], true)
            ? \App\Models\Clinic::query()->pluck('id')
            : $user->staffClinics()->pluck('clinics.id');
        if ($request->filled('clinic_id')) {
            $selectedClinic = (int)$request->query('clinic_id');
            abort_unless($clinicIds->contains($selectedClinic),403,'Bu klinika siziň hasabyňyza birikdirilmedik.');
            $clinicIds = collect([$selectedClinic]);
        }

        $payments = Payment::query()
            ->with(['appointment:id,booking_code,patient_id,doctor_profile_id,clinic_id,appointment_date,start_time,end_time,status', 'appointment.patient:id,name,phone', 'appointment.doctor.user:id,name', 'appointment.clinic:id,name', 'histories.actor:id,name'])
            ->whereHas('appointment', fn ($q) => $q->whereIn('clinic_id', $clinicIds))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')->toString()))
            ->latest()
            ->paginate(20)->withQueryString();

        return response()->json($payments);
    }

    public function update(Request $request, Payment $payment)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['pending', 'paid', 'refunded', 'cancelled'])],
            'payment_method' => ['sometimes', Rule::in(['cash'])],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $user = $request->user();
        abort_unless(in_array(strtolower((string)$user->role), ['admin', 'superadmin', 'super_admin'], true), 403, 'Tölegleri üýtgetmäge rugsadyňyz ýok.');
        $appointment = $payment->appointment()->with('doctor')->firstOrFail();
        $permission = $data['status'] === 'refunded' ? 'payments.refund' : ($data['status'] === 'paid' ? 'payments.mark_paid' : 'payments.update');
        abort_unless($user->hasPermission($permission, (int) $appointment->clinic_id), 403);
        if (!in_array(strtolower((string)$user->role), ['superadmin','super_admin'], true)) {
            abort_unless($user->staffClinics()->where('clinics.id', $appointment->clinic_id)->exists(), 403, 'Bu klinika üçin rugsadyňyz ýok.');
        }

        $updated = DB::transaction(function () use ($payment, $data, $user) {
            $locked = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();
            $oldStatus = $locked->status;
            $oldMethod = $locked->payment_method;
            $newStatus = $data['status'];
            $newMethod = $data['payment_method'] ?? $oldMethod;

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
                'actor_user_id' => $user->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'old_payment_method' => $oldMethod,
                'new_payment_method' => $newMethod,
                'note' => $data['note'] ?? null,
                'reason' => $data['note'] ?? null,
                'amount' => $locked->amount,
            ]);

            return $locked->fresh(['histories.actor']);
        });

        return response()->json(['message' => 'Töleg maglumatlary täzelendi.', 'data' => $updated]);
    }
}
