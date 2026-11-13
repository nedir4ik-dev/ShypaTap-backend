<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Clinic;
use App\Models\DoctorProfile;
use App\Models\Appointment;

class DashboardController extends Controller
{
    public function index()
    {
        $totalPatients = User::where('role', 'patient')->count();

        $totalDoctors = DoctorProfile::count();

        $totalClinics = Clinic::count();

        $recentAppointments = Appointment::latest()
            ->take(8)
            ->get();

        return view('admin.dashboard', compact(
            'totalPatients',
            'totalDoctors',
            'totalClinics',
            'recentAppointments'
        ));
    }
}
