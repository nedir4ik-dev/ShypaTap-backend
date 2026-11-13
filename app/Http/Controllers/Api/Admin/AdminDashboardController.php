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
        $paid = Payment::where('status', 'paid');
        return response()->json([
            'total_users' => User::count(),
            'total_patients' => User::where('role', 'patient')->count(),
            'total_doctors' => DoctorProfile::count(),
            'total_receptionists' => User::where('role', 'receptionist')->count(),
            'total_clinics' => Clinic::count(),
            'total_appointments' => Appointment::count(),
            'pending_appointments' => Appointment::where('status', 'pending')->count(),
            'confirmed_appointments' => Appointment::where('status', 'confirmed')->count(),
            'cancelled_appointments' => Appointment::where('status', 'cancelled')->count(),
            'completed_appointments' => Appointment::where('status', 'completed')->count(),
            'paid_total' => (clone $paid)->sum('amount'),
            'paid_count' => (clone $paid)->count(),
            'revenue_by_month' => Payment::where('status', 'paid')->whereNotNull('paid_at')->selectRaw("to_char(paid_at, 'YYYY-MM') as month, SUM(amount) as total")->groupBy('month')->orderBy('month')->get(),
            'recent_appointments' => Appointment::with(['patient:id,name', 'doctor.user:id,name', 'clinic:id,name'])->latest()->take(8)->get(),
        ]);
    }
}
