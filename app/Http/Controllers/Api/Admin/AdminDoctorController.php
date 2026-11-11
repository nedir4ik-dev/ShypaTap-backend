<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\DoctorProfile;
use App\Models\Specialization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminDoctorController extends Controller
{
    public function index(Request $request)
    {
        $query = DoctorProfile::with([
            'user:id,name,email,phone,is_active',
            'specialization:id,name',
            'clinics:id,name,city',
        ]);

        if ($request->filled('specialization_id')) {
            $query->where('specialization_id', $request->integer('specialization_id'));
        }
        if ($request->filled('clinic_id')) {
            $clinicId = $request->integer('clinic_id');
            $query->whereHas('clinics', fn($q) => $q->whereKey($clinicId));
        }
        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->whereHas('user', fn($q) => $q->where('name', 'ilike', "%{$search}%"));
        }

        $doctors = $query->latest()->paginate(15);

        // Eger sorag API (JSON) arkaly gelse:
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json($doctors);
        }

        // Brauzerden girilende Blade sahypasyny görkezýäris:
        return view('admin.doctors.index', compact('doctors'));
    }

    public function create(Request $request)
    {
        $specializations = Specialization::all();
        $clinics = Clinic::all();

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'specializations' => $specializations,
                'clinics' => $clinics,
            ]);
        }

        return view('admin.doctors.create', compact('specializations', 'clinics'));
    }

    public function show(Request $request, DoctorProfile $doctor)
    {
        $doctor->load(['user:id,name,email,phone,is_active', 'specialization', 'clinics']);

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json(['data' => $doctor]);
        }

        return view('admin.doctors.show', compact('doctor'));
    }

    public function edit(Request $request, DoctorProfile $doctor)
    {
        $doctor->load(['user', 'specialization', 'clinics']);
        $specializations = Specialization::all();
        $clinics = Clinic::all();

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'doctor' => $doctor,
                'specializations' => $specializations,
                'clinics' => $clinics,
            ]);
        }

        return view('admin.doctors.edit', compact('doctor', 'specializations', 'clinics'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:8'],
            'specialization_id' => ['required', 'integer', 'exists:specializations,id'],
            'experience_years' => ['sometimes', 'integer', 'min:0', 'max:70'],
            'education' => ['nullable', 'string'],
            'biography' => ['nullable', 'string'],
            'consultation_fee' => ['sometimes', 'numeric', 'min:0'],
            'currency' => ['sometimes', 'string', 'size:3'],
            'is_verified' => ['sometimes', 'boolean'],
            'clinic_ids' => ['sometimes', 'array'],
            'clinic_ids.*' => ['integer', 'distinct', 'exists:clinics,id'],
        ]);

        $doctor = DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => Hash::make($data['password']),
                'role' => 'doctor',
                'is_active' => true,
            ]);

            $doctor = DoctorProfile::create([
                'user_id' => $user->id,
                'specialization_id' => $data['specialization_id'],
                'experience_years' => $data['experience_years'] ?? 0,
                'education' => $data['education'] ?? null,
                'biography' => $data['biography'] ?? null,
                'consultation_fee' => $data['consultation_fee'] ?? 0,
                'currency' => $data['currency'] ?? 'TMT',
                'is_verified' => $data['is_verified'] ?? false,
            ]);

            $doctor->clinics()->sync($data['clinic_ids'] ?? []);

            return $doctor;
        });

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Lukman döredildi.',
                'data' => $doctor->load(['user:id,name,email,phone', 'specialization', 'clinics']),
            ], 201);
        }

        return redirect()->route('admin.doctors.index')->with('success', 'Lukman üstünlikli döredildi.');
    }

    public function update(Request $request, DoctorProfile $doctor)
    {
        $user = $doctor->user;

        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['sometimes', 'nullable', 'string', 'max:30', Rule::unique('users', 'phone')->ignore($user->id)],
            'password' => ['sometimes', 'required', 'string', 'min:8'],
            'is_active' => ['sometimes', 'boolean'],
            'specialization_id' => ['sometimes', 'required', 'integer', 'exists:specializations,id'],
            'experience_years' => ['sometimes', 'integer', 'min:0', 'max:70'],
            'education' => ['sometimes', 'nullable', 'string'],
            'biography' => ['sometimes', 'nullable', 'string'],
            'consultation_fee' => ['sometimes', 'numeric', 'min:0'],
            'currency' => ['sometimes', 'string', 'size:3'],
            'is_verified' => ['sometimes', 'boolean'],
            'clinic_ids' => ['sometimes', 'array'],
            'clinic_ids.*' => ['integer', 'distinct', 'exists:clinics,id'],
        ]);

        DB::transaction(function () use ($data, $doctor, $user) {
            $userData = array_intersect_key($data, array_flip(['name', 'email', 'phone', 'is_active', 'password']));
            if (isset($userData['password'])) {
                $userData['password'] = Hash::make($userData['password']);
            }
            if ($userData) {
                $user->update($userData);
            }

            $profileData = array_intersect_key($data, array_flip([
                'specialization_id',
                'experience_years',
                'education',
                'biography',
                'consultation_fee',
                'currency',
                'is_verified',
            ]));
            if ($profileData) {
                $doctor->update($profileData);
            }
            if (array_key_exists('clinic_ids', $data)) {
                $doctor->clinics()->sync($data['clinic_ids']);
            }
        });

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Lukman maglumatlary täzelendi.',
                'data' => $doctor->fresh()->load(['user:id,name,email,phone,is_active', 'specialization', 'clinics']),
            ]);
        }

        return redirect()->route('admin.doctors.index')->with('success', 'Lukman maglumatlary üstünlikli üýtgedildi.');
    }

    public function updateVerification(Request $request, DoctorProfile $doctor)
    {
        $data = $request->validate(['is_verified' => ['required', 'boolean']]);
        $doctor->update($data);

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json(['message' => 'Lukmanyň tassyklama ýagdaýy täzelendi.', 'data' => $doctor->fresh()]);
        }

        return back()->with('success', 'Lukmanyň tassyklama ýagdaýy üýtgedildi.');
    }
}
