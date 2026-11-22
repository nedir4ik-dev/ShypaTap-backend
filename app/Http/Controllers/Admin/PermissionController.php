<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\DoctorProfile;
use App\Models\User;

class PermissionController extends Controller
{
    public function index()
    {
        return view('admin.permissions.index', [
            'clinics' => Clinic::orderBy('name')->get(),
            'receptionists' => User::where('role', 'receptionist')->with('staffClinics')->orderBy('name')->get(),
            'doctors' => DoctorProfile::with(['user:id,name', 'clinics'])->orderBy('id')->get(),
            'staffPermissions' => [
                'dashboard.view' => 'Dashboard görmek',
                'appointments.view' => 'Bronlary görmek',
                'appointments.create' => 'Bron döretmek',
                'patients.view' => 'Näsaglary görmek',
                'doctors.view' => 'Lukmanlary görmek',
                'payments.view' => 'Tölegleri görmek',
                'payments.manage' => 'Tölegleri dolandyrmak / refund',
            ],
            'doctorPermissions' => [
                'dashboard.view' => 'Dashboard görmek',
                'appointments.view' => 'Bronlary görmek',
                'appointments.manage' => 'Bron ýagdaýyny üýtgetmek',
                'schedules.view' => 'Iş tertibini görmek',
                'schedules.manage' => 'Iş tertibini dolandyrmak',
                'reviews.view' => 'Bahalandyrmalary görmek',
                'payments.manage' => 'Tölegleri dolandyrmak / refund',
            ],
        ]);
    }
}
