<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Models\Payment;
use App\Models\Review;
use Illuminate\Support\Str;

class AdminAppointmentController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate([
            'status' => ['sometimes', 'string', Rule::in(Appointment::STATUSES)],
            'date_from' => ['sometimes', 'date'],
            'date_to' => ['sometimes', 'date', 'after_or_equal:date_from'],
            'clinic_id' => ['sometimes', 'integer', 'exists:clinics,id'],
            'doctor_profile_id' => ['sometimes', 'integer', 'exists:doctor_profiles,id'],
        ]);

        $clinicIds = in_array(strtolower((string)$request->user()->role), ['superadmin','super_admin'], true) ? \App\Models\Clinic::pluck('id') : $request->user()->staffClinics()->pluck('clinics.id');
        if (isset($data['clinic_id'])) abort_unless($clinicIds->contains((int)$data['clinic_id']),403);
        $query = Appointment::whereIn('clinic_id',$clinicIds)->with([
            'patient:id,name,email,phone',
            'doctor.user:id,name',
            'doctor.specialization:id,name',
            'clinic:id,name,city',
            'createdBy:id,name,role',
        ]);

        foreach (['status', 'clinic_id', 'doctor_profile_id'] as $field) {
            if (isset($data[$field])) {
                $query->where($field, $data[$field]);
            }
        }
        if (isset($data['date_from'])) {
            $query->whereDate('appointment_date', '>=', $data['date_from']);
        }
        if (isset($data['date_to'])) {
            $query->whereDate('appointment_date', '<=', $data['date_to']);
        }

        $appointments = $query->orderByDesc('appointment_date')->orderByDesc('start_time')->paginate(20)->withQueryString();

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json($appointments);
        }

        return view('admin.appointments.index', compact('appointments'));
    }

    public function show(Request $request, Appointment $appointment)
    {
        abort_unless($request->user()->belongsToClinic((int)$appointment->clinic_id),403);
        $appointment->load([
            'patient:id,name,email,phone,avatar_path',
            'doctor.user:id,name,email,phone,avatar_path',
            'doctor.specialization:id,name',
            'clinic:id,name,city,address,phone',
            'createdBy:id,name,role',
            'payment',
            'review',
        ]);

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json(['data' => $appointment]);
        }

        return view('admin.appointments.show', compact('appointment'));
    }

    public function update(Request $request, Appointment $appointment)
    {
        abort_unless($request->user()->belongsToClinic((int)$appointment->clinic_id),403);
        $data = $request->validate([
            'status' => ['required', Rule::in(Appointment::STATUSES)],
            'cancellation_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $allowed = [
            'pending' => ['confirmed', 'cancelled', 'rejected'],
            'confirmed' => ['checked_in', 'cancelled', 'completed', 'no_show'],
            'checked_in' => ['completed'],
        ];

        if (!in_array($data['status'], $allowed[$appointment->status] ?? [], true)) {
            if ($request->wantsJson() || $request->is('api/*')) {
                abort(422, 'Bu status geçişine rugsat berilmeýär.');
            }
            return back()->with('error', 'Bu status geçişine rugsat berilmeýär.');
        }

        $updates = ['status' => $data['status']];
        if ($data['status'] === 'confirmed') {
            $updates['confirmed_at'] = now();
        } elseif (in_array($data['status'], ['cancelled', 'rejected'], true)) {
            $updates['cancelled_at'] = now();
            if (!empty($data['cancellation_reason'])) {
                $updates['cancellation_reason'] = $data['cancellation_reason'];
            }
        } elseif ($data['status'] === 'completed') {
            $updates['completed_at'] = now();
        }

        DB::transaction(function () use ($appointment, $updates, $request, $data) {
            $locked = Appointment::whereKey($appointment->id)->lockForUpdate()->firstOrFail();
            $locked->update($updates);
            if (in_array($data['status'], ['cancelled','rejected'], true)) {
                $payment = Payment::where('appointment_id',$locked->id)->lockForUpdate()->first();
                if ($payment && $payment->status === 'pending') {
                    $payment->update(['status'=>'cancelled']);
                    $payment->histories()->create(['actor_user_id'=>$request->user()->id,'old_status'=>'pending','new_status'=>'cancelled','old_payment_method'=>$payment->payment_method,'new_payment_method'=>$payment->payment_method,'note'=>'Admin tarapyndan bron ýatyryldy.','reason'=>'Admin tarapyndan bron ýatyryldy.','amount'=>$payment->amount]);
                }
            }
        });
        $appointment->refresh();

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json(['message' => 'Kabul ýazgysy täzelendi.', 'data' => $appointment->fresh()]);
        }

        return back()->with('success', 'Bron ýagdaýy üstünlikli täzelendi.');
    }

    public function destroy(Request $request, Appointment $appointment)
    {
        abort_unless($request->user()->belongsToClinic((int)$appointment->clinic_id),403);
        DB::transaction(function () use ($appointment) {
            $batch=(string) Str::uuid(); $now=now();
            Review::where('appointment_id',$appointment->id)->whereNull('deleted_at')->update(['deleted_at'=>$now,'trash_batch_id'=>$batch]);
            $appointment->forceFill(['trash_batch_id'=>$batch])->save();
            $appointment->delete();
        });
        return $request->expectsJson() || $request->is('api/*')
            ? response()->json(['message'=>'Kabul ýazgysy we degişli bahalandyrma Trash bölümine geçirildi.'])
            : back()->with('success','Kabul ýazgysy Trash bölümine geçirildi.');
    }

}
