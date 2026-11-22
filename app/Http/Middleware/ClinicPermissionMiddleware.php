<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Appointment;
use App\Models\Payment;
use App\Models\DoctorSchedule;
use App\Models\DoctorDayOff;

class ClinicPermissionMiddleware
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Ulgama girmek gerek.'], 401);
        }
        if ($user->role === 'admin') {
            return $next($request);
        }

        $clinicId = $request->input('clinic_id') ?: $request->route('clinic');
        $appointmentId = $request->route('id') ?: $request->route('appointment');
        $paymentId = $request->route('payment');
        if ($appointmentId instanceof Appointment) $appointmentId = $appointmentId->getKey();
        if ($paymentId instanceof Payment) $paymentId = $paymentId->getKey();

        if (! $clinicId && $appointmentId) {
            if (str_contains($request->path(), 'days-off')) {
                $clinicId = DoctorDayOff::whereKey($appointmentId)->value('clinic_id');
            } elseif (str_contains($request->path(), 'schedules')) {
                $clinicId = DoctorSchedule::whereKey($appointmentId)->value('clinic_id');
            } else {
                $clinicId = Appointment::whereKey($appointmentId)->value('clinic_id');
            }
        }
        if (! $clinicId && $paymentId) {
            $clinicId = Payment::whereKey($paymentId)->with('appointment:id,clinic_id')->first()?->appointment?->clinic_id;
        }

        if ($user->role === 'doctor') {
            $profile = $user->doctorProfile;
            if (! $profile) {
                return response()->json(['message' => 'Lukman profili tapylmady.'], 403);
            }
            $assignmentsQuery = $profile->clinics();
        } else {
            $assignmentsQuery = $user->staffClinics();
        }
        if ($clinicId) {
            $assignmentsQuery->where('clinics.id', $clinicId);
        }

        $assignments = $assignmentsQuery->get();
        if ($assignments->isEmpty()) {
            return response()->json(['message' => 'Bu klinika üçin rugsadyňyz ýok.'], 403);
        }

        // Null permissions preserves legacy assigned staff access; once configured,
        // the permission must be explicitly present for every clinic in this request.
        foreach ($assignments as $clinic) {
            $permissions = $clinic->pivot->permissions;
            if (is_array($permissions) && ! in_array($permission, $permissions, true)) {
                return response()->json(['message' => 'Bu amaly ýerine ýetirmäge rugsadyňyz ýok: '.$permission], 403);
            }
        }

        return $next($request);
    }
}
