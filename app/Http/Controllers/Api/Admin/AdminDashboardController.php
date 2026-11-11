<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Clinic;
use App\Models\Appointment;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        $totalPatients = User::where('role', 'patient')->count();
        $totalDoctors = User::where('role', 'doctor')->count();
        $totalClinics = Clinic::count();
        $recentAppointments = Appointment::latest()->take(5)->get();

        // Eger sorag API (JSON) arkaly gelse:
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'total_patients' => $totalPatients,
                'total_doctors' => $totalDoctors,
                'total_clinics' => $totalClinics,
                'recent_appointments' => $recentAppointments,
            ]);
        }

        // Brauzerden girilende Blade sahypasyny we maglumatlary iberýäris:
        return view('admin.dashboard', compact(
            'totalPatients',
            'totalDoctors',
            'totalClinics',
            'recentAppointments'
        ));
    }
}
