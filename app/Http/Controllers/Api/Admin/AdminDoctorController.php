<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\DoctorProfile;
use App\Models\Specialization;
use App\Models\User;
use App\Services\AvatarService;
use App\Services\RoleAssignmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class AdminDoctorController extends Controller
{
    public function index(Request $request)
    {
        $clinicIds = in_array(strtolower((string)$request->user()->role), ['superadmin','super_admin'], true) ? Clinic::pluck('id') : $request->user()->staffClinics()->pluck('clinics.id');
        $query = DoctorProfile::whereHas('clinics',fn($q)=>$q->whereIn('clinics.id',$clinicIds))->with([
            'user:id,name,email,phone,is_active,avatar_path',
            'specialization:id,name',
            'clinics:id,name,city',
        ]);

        if ($request->filled('specialization_id')) {
            $query->where('specialization_id', $request->integer('specialization_id'));
        }
        if ($request->filled('clinic_id')) {
            $clinicId = $request->integer('clinic_id');
            abort_unless($clinicIds->contains($clinicId),403);
            $query->whereHas('clinics', fn($q) => $q->whereKey($clinicId));
        }
        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->whereHas('user', fn($q) => $q->where('name', 'ilike', "%{$search}%"));
        }

        $doctors = $query->latest()->paginate(15);

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json($doctors);
        }

        return view('admin.doctors.index', compact('doctors'));
    }

    public function create(Request $request)
    {
        $specializations = Specialization::all();
        $clinics = in_array(strtolower((string)$request->user()->role), ['superadmin','super_admin'], true) ? Clinic::all() : Clinic::whereIn('id',$request->user()->staffClinics()->pluck('clinics.id'))->get();

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
        abort_unless($doctor->clinics()->whereIn('clinics.id', in_array(strtolower((string)$request->user()->role), ['superadmin','super_admin'], true) ? Clinic::pluck('id') : $request->user()->staffClinics()->pluck('clinics.id'))->exists(),403);
        $doctor->load(['user:id,name,email,phone,is_active,avatar_path', 'specialization', 'clinics']);

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json(['data' => $doctor]);
        }

        return view('admin.doctors.show', compact('doctor'));
    }

    public function edit(Request $request, DoctorProfile $doctor)
    {
        abort_unless($doctor->clinics()->whereIn('clinics.id', in_array(strtolower((string)$request->user()->role), ['superadmin','super_admin'], true) ? Clinic::pluck('id') : $request->user()->staffClinics()->pluck('clinics.id'))->exists(),403);
        $doctor->load(['user', 'specialization', 'clinics']);
        $specializations = Specialization::all();
        $clinics = in_array(strtolower((string)$request->user()->role), ['superadmin','super_admin'], true) ? Clinic::all() : Clinic::whereIn('id',$request->user()->staffClinics()->pluck('clinics.id'))->get();

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'doctor' => $doctor,
                'specializations' => $specializations,
                'clinics' => $clinics,
            ]);
        }

        return view('admin.doctors.edit', compact('doctor', 'specializations', 'clinics'));
    }

    public function store(Request $request, AvatarService $avatars, RoleAssignmentService $roles)
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
            'avatar' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png', 'max:3072'],
        ]);

        $clinicIds = in_array(strtolower((string)$request->user()->role), ['superadmin','super_admin'], true) ? Clinic::pluck('id') : $request->user()->staffClinics()->pluck('clinics.id');
        foreach (($data['clinic_ids'] ?? []) as $clinicId) abort_unless($clinicIds->contains((int)$clinicId),403);
        if (!in_array(strtolower((string)$request->user()->role), ['superadmin','super_admin'], true)) abort_if(empty($data['clinic_ids']),422,'Lukmany azyndan bir klinika belläň.');
        $avatarFile = $request->file('avatar');
        unset($data['avatar']);

        $doctor = DB::transaction(function () use ($data, $roles) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => Hash::make($data['password']),
                'role' => 'doctor',
                'is_active' => true,
            ]);

            $roles->assignDefault($user);

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

        if ($avatarFile) {
            $avatars->replace($doctor->user, $avatarFile);
        }

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Lukman döredildi.',
                'data' => $doctor->load(['user:id,name,email,phone,avatar_path', 'specialization', 'clinics']),
            ], 201);
        }

        return redirect()->route('admin.doctors.index')->with('success', 'Lukman üstünlikli döredildi.');
    }

    public function update(Request $request, DoctorProfile $doctor, AvatarService $avatars)
    {
        abort_unless($doctor->clinics()->whereIn('clinics.id', in_array(strtolower((string)$request->user()->role), ['superadmin','super_admin'], true) ? Clinic::pluck('id') : $request->user()->staffClinics()->pluck('clinics.id'))->exists(),403);
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
            'avatar' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png', 'max:3072'],
            'remove_avatar' => ['sometimes', 'boolean'],
        ]);

        $allowedClinicIds = in_array(strtolower((string)$request->user()->role), ['superadmin','super_admin'], true) ? Clinic::pluck('id') : $request->user()->staffClinics()->pluck('clinics.id');
        foreach (($data['clinic_ids'] ?? []) as $clinicId) abort_unless($allowedClinicIds->contains((int)$clinicId),403);
        if (array_key_exists('clinic_ids',$data) && !in_array(strtolower((string)$request->user()->role), ['superadmin','super_admin'], true)) abort_if(empty($data['clinic_ids']),422,'Lukman azyndan bir klinikada işlemeli.');
        $avatarFile = $request->file('avatar');
        $removeAvatar = $request->boolean('remove_avatar');
        unset($data['avatar'], $data['remove_avatar']);

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

        if ($avatarFile) {
            $avatars->replace($user->fresh(), $avatarFile);
        } elseif ($removeAvatar) {
            $avatars->remove($user->fresh());
        }

        if (array_key_exists('is_active', $data) && ! $user->fresh()->is_active) {
            $user->tokens()->delete();
        }

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Lukman maglumatlary täzelendi.',
                'data' => $doctor->fresh()->load(['user:id,name,email,phone,is_active,avatar_path', 'specialization', 'clinics']),
            ]);
        }

        return redirect()->route('admin.doctors.index')->with('success', 'Lukman maglumatlary üstünlikli üýtgedildi.');
    }

    public function updateVerification(Request $request, DoctorProfile $doctor)
    {
        abort_unless($doctor->clinics()->whereIn('clinics.id', in_array(strtolower((string)$request->user()->role), ['superadmin','super_admin'], true) ? Clinic::pluck('id') : $request->user()->staffClinics()->pluck('clinics.id'))->exists(),403);
        $data = $request->validate(['is_verified' => ['required', 'boolean']]);
        $doctor->update($data);

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json(['message' => 'Lukmanyň tassyklama ýagdaýy täzelendi.', 'data' => $doctor->fresh()]);
        }

        return back()->with('success', 'Lukmanyň tassyklama ýagdaýy üýtgedildi.');
    }

    public function destroy(Request $request, DoctorProfile $doctor)
    {
        $allowedClinicIds = in_array(strtolower((string)$request->user()->role), ['superadmin','super_admin'], true) ? Clinic::pluck('id') : $request->user()->staffClinics()->pluck('clinics.id');
        abort_unless($doctor->clinics()->whereIn('clinics.id',$allowedClinicIds)->exists(),403);
        abort_unless($doctor->clinics()->whereNotIn('clinics.id',$allowedClinicIds)->doesntExist(),403,'Lukman başga klinikalara hem birikdirilen. Ähli klinikalara rugsat gerek.');
        abort_if(in_array(strtolower((string)$doctor->user?->role), ['superadmin','super_admin'], true), 403, 'SuperAdmin hasabyny lukman hökmünde pozup bolmaýar.');
        $batch = (string) Str::uuid();
        DB::transaction(function () use ($doctor, $batch) {
            $now=now();
            $appointmentIds=\App\Models\Appointment::where('doctor_profile_id',$doctor->id)->whereNull('deleted_at')->pluck('id');
            \App\Models\Appointment::whereIn('id',$appointmentIds)->update(['deleted_at'=>$now,'trash_batch_id'=>$batch]);
            \App\Models\Review::whereIn('appointment_id',$appointmentIds)->whereNull('deleted_at')->update(['deleted_at'=>$now,'trash_batch_id'=>$batch]);
            $doctor->forceFill(['trash_batch_id'=>$batch])->save();
            $doctor->delete();
            $user = $doctor->user;
            if ($user && !$user->trashed()) {
                $user->forceFill(['trash_batch_id'=>$batch])->save();
                $user->tokens()->delete();
                $user->delete();
            }
        });
        return response()->json(['message'=>'Lukman we onuň hasaby Trash bölümine geçirildi.']);
    }
}
