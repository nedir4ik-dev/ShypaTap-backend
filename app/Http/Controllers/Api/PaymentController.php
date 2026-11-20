<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    public function update(Request $request, Payment $payment)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['pending', 'paid', 'refunded', 'cancelled'])],
            'payment_method' => ['sometimes', Rule::in(['cash'])],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $user = $request->user();
        $appointment = $payment->appointment()->with('doctor')->firstOrFail();

        if ($user->role === 'doctor') {
            abort_unless($appointment->doctor?->user_id === $user->id, 403, 'Bu töleg siziň kabul ýazgyňyza degişli däl.');
        } elseif ($user->role === 'receptionist') {
            abort_unless($user->staffClinics()->where('clinics.id', $appointment->clinic_id)->exists(), 403, 'Bu klinika üçin rugsadyňyz ýok.');
        } elseif ($user->role !== 'admin') {
            abort(403);
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
                'paid_at' => $newStatus === 'paid' ? ($locked->paid_at ?? now()) : null,
            ]);

            $locked->histories()->create([
                'actor_user_id' => $user->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'old_payment_method' => $oldMethod,
                'new_payment_method' => $newMethod,
                'note' => $data['note'] ?? null,
            ]);

            return $locked->fresh(['histories.actor']);
        });

        return response()->json(['message' => 'Töleg maglumatlary täzelendi.', 'data' => $updated]);
    }
}
