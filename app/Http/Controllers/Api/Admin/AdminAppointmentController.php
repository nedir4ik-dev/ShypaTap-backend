<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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

        $query = Appointment::with([
            'patient:id,name,email,phone',
            'doctor.user:id,name',
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

        $appointments = $query->orderByDesc('appointment_date')->orderByDesc('start_time')->paginate(20);

        // Eger sorag API (JSON) arkaly gelse:
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json($appointments);
        }

        // Brauzerden girilende Blade sahypasyny görkezýäris:
        return view('admin.appointments.index', compact('appointments'));
    }

    public function show(Request $request, Appointment $appointment)
    {
        $appointment->load([
            'patient:id,name,email,phone',
            'doctor.user:id,name',
            'clinic:id,name,city',
            'createdBy:id,name,role',
            'review',
        ]);

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json(['data' => $appointment]);
        }

        return view('admin.appointments.show', compact('appointment'));
    }
}
