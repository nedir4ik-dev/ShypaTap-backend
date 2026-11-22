<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Clinic;
use App\Models\DoctorProfile;
use App\Models\Appointment;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'clinic_id' => ['sometimes', 'integer', 'exists:clinics,id'],
            'doctor_profile_id' => ['sometimes', 'integer', 'exists:doctor_profiles,id'],
            'month' => ['sometimes', 'date_format:Y-m'],
            'payment_status' => ['sometimes', 'in:pending,paid,refunded,cancelled'],
            'status' => ['sometimes', 'in:pending,confirmed,cancelled,rejected,completed,no_show'],
        ]);

        $appointments = Appointment::query()
            ->when(isset($filters['clinic_id']), fn ($q) => $q->where('clinic_id', $filters['clinic_id']))
            ->when(isset($filters['doctor_profile_id']), fn ($q) => $q->where('doctor_profile_id', $filters['doctor_profile_id']))
            ->when(isset($filters['month']), fn ($q) => $q->whereRaw("to_char(appointment_date, 'YYYY-MM') = ?", [$filters['month']]))
            ->when(isset($filters['status']), fn ($q) => $q->where('status', $filters['status']));

        $paymentQuery = Payment::query()->whereHas('appointment', function ($q) use ($filters) {
            $q->when(isset($filters['clinic_id']), fn ($q) => $q->where('clinic_id', $filters['clinic_id']))
                ->when(isset($filters['doctor_profile_id']), fn ($q) => $q->where('doctor_profile_id', $filters['doctor_profile_id']))
                ->when(isset($filters['month']), fn ($q) => $q->whereRaw("to_char(appointment_date, 'YYYY-MM') = ?", [$filters['month']]));
        })->when(isset($filters['payment_status']), fn ($q) => $q->where('status', $filters['payment_status']));

        $paid = (clone $paymentQuery)->where('status', 'paid');
        return response()->json([
            'total_users' => User::count(),
            'total_patients' => User::where('role', 'patient')->count(),
            'total_doctors' => DoctorProfile::count(),
            'total_receptionists' => User::where('role', 'receptionist')->count(),
            'total_clinics' => Clinic::count(),
            'total_appointments' => (clone $appointments)->count(),
            'pending_appointments' => (clone $appointments)->where('status', 'pending')->count(),
            'confirmed_appointments' => (clone $appointments)->where('status', 'confirmed')->count(),
            'cancelled_appointments' => (clone $appointments)->where('status', 'cancelled')->count(),
            'completed_appointments' => (clone $appointments)->where('status', 'completed')->count(),
            'paid_total' => (clone $paid)->sum('amount'),
            'paid_count' => (clone $paid)->count(),
            'revenue_by_month' => (clone $paid)->whereNotNull('paid_at')->selectRaw("to_char(paid_at, 'YYYY-MM') as month, SUM(amount) as total")->groupBy('month')->orderBy('month')->get(),
            'recent_appointments' => (clone $appointments)->with(['patient:id,name', 'doctor.user:id,name', 'clinic:id,name'])->latest()->take(8)->get(),
            'filters' => $filters,
        ]);
    }
    public function show(Appointment $appointment)
    {
        $appointment->load(['patient', 'doctor.user', 'doctor.specialization', 'clinic']);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $appointment->id,
                'appointment_number' => $appointment->booking_code ?? 'ST-' . $appointment->id,
                'status' => $appointment->status,
                'appointment_date' => $appointment->appointment_date,
                'start_time' => $appointment->start_time,
                'end_time' => $appointment->end_time,
                'patient' => [
                    'id' => $appointment->patient?->id,
                    'name' => $appointment->patient?->name,
                    'phone' => $appointment->patient?->phone,
                    'email' => $appointment->patient?->email,
                ],
                'doctor' => [
                    'id' => $appointment->doctor?->id,
                    'name' => $appointment->doctor?->user?->name,
                    'specialization' => $appointment->doctor?->specialization?->name ?? 'Görkezilmedik',
                ],
                'clinic' => [
                    'id' => $appointment->clinic?->id,
                    'name' => $appointment->clinic?->name,
                ],
                'symptoms' => $appointment->reason ?? $appointment->notes ?? 'Şikaýat ýazylmady',
                'created_at' => $appointment->created_at?->format('d.m.Y H:i'),
            ]
        ]);
    }
}
