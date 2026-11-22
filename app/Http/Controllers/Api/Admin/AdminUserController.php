<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\AvatarService;
use App\Models\User;
use App\Models\Clinic;
use App\Models\Specialization;
use App\Models\DoctorProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $query = ($request->boolean('archived') ? User::onlyTrashed() : User::query())->select([
            'id',
            'name',
            'email',
            'phone',
            'avatar_path',
            'role',
            'is_active',
            'created_at',
        ]);

        if ($request->filled('role')) {
            $query->where('role', $request->string('role')->toString());
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(15)->withQueryString();

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json($users);
        }

        return view('admin.users.index', compact('users'));
    }

    public function create(Request $request)
    {
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json(['message' => 'API üçin bu ýol ýok.']);
        }

        return view('admin.users.create', [
            'specializations' => Specialization::orderBy('name')->get(),
            'clinics' => Clinic::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function show(Request $request, User $user)
    {
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'data' => $user->only(['id', 'name', 'email', 'phone', 'avatar_url', 'role', 'is_active', 'created_at']),
            ]);
        }

        return view('admin.users.show', compact('user'));
    }

    public function edit(Request $request, User $user)
    {
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json(['data' => $user]);
        }

        return view('admin.users.edit', compact('user'));
    }

    public function store(Request $request, AvatarService $avatars)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', Rule::in(['patient', 'doctor', 'receptionist', 'admin'])],
            'is_active' => ['sometimes', 'boolean'],
            'specialization_id' => ['required_if:role,doctor', 'nullable', 'integer', 'exists:specializations,id'],
            'experience_years' => ['nullable', 'integer', 'min:0', 'max:70'],
            'education' => ['nullable', 'string'],
            'biography' => ['nullable', 'string'],
            'consultation_fee' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'clinic_ids' => ['required_if:role,doctor,receptionist', 'array', 'min:1'],
            'clinic_ids.*' => ['integer', 'distinct', 'exists:clinics,id'],
            'avatar' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png', 'max:3072'],
        ]);

        $avatarFile = $request->file('avatar');
        unset($data['avatar']);
        $data['is_active'] = $request->has('is_active');
        $data['password'] = Hash::make($data['password']);

        $clinicIds = $data['clinic_ids'] ?? [];
        $doctorData = array_intersect_key($data, array_flip([
            'specialization_id', 'experience_years', 'education', 'biography', 'consultation_fee', 'currency',
        ]));
        unset($data['clinic_ids'], $data['specialization_id'], $data['experience_years'], $data['education'],
            $data['biography'], $data['consultation_fee'], $data['currency']);

        $user = DB::transaction(function () use ($data, $clinicIds, $doctorData) {
            $user = User::create($data);
            if ($user->role === 'doctor') {
                $doctor = DoctorProfile::create([
                    'user_id' => $user->id,
                    'specialization_id' => $doctorData['specialization_id'],
                    'experience_years' => $doctorData['experience_years'] ?? 0,
                    'education' => $doctorData['education'] ?? null,
                    'biography' => $doctorData['biography'] ?? null,
                    'consultation_fee' => $doctorData['consultation_fee'] ?? 0,
                    'currency' => $doctorData['currency'] ?? 'TMT',
                    'is_verified' => false,
                ]);
                $doctor->clinics()->sync($clinicIds);
            } elseif ($user->role === 'receptionist' && $clinicIds) {
                $user->staffClinics()->sync($clinicIds);
            }
            return $user;
        });

        if ($avatarFile) {
            $avatars->replace($user, $avatarFile);
            $user->refresh();
        }

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Ulanyjy döredildi.',
                'data' => $user->only(['id', 'name', 'email', 'phone', 'avatar_url', 'role', 'is_active']),
            ], 201);
        }

        return redirect()->route('admin.users.index')->with('success', 'Ulanyjy üstünlikli döredildi.');
    }

    public function update(Request $request, User $user, AvatarService $avatars)
    {
        $actor = $request->user();

        if ($actor->id === $user->id && $request->has('role') && $request->input('role') !== 'admin') {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'Öz admin roluňyzy üýtgedip bilmersiňiz.'], 422);
            }
            return back()->withErrors(['role' => 'Öz admin roluňyzy üýtgedip bilmersiňiz.']);
        }

        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['sometimes', 'nullable', 'string', 'max:30', Rule::unique('users', 'phone')->ignore($user->id)],
            'password' => ['sometimes', 'required', 'string', 'min:8'],
            'role' => ['sometimes', 'required', Rule::in(['patient', 'doctor', 'receptionist', 'admin'])],
            'is_active' => ['sometimes', 'boolean'],
            'avatar' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png', 'max:3072'],
            'remove_avatar' => ['sometimes', 'boolean'],
        ]);

        if ($actor->id === $user->id && array_key_exists('is_active', $data) && !$data['is_active']) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'Öz hasabyňyzy öçürip bilmersiňiz.'], 422);
            }
            return back()->withErrors(['is_active' => 'Öz hasabyňyzy öçürip bilmersiňiz.']);
        }

        $avatarFile = $request->file('avatar');
        $removeAvatar = $request->boolean('remove_avatar');
        unset($data['avatar'], $data['remove_avatar']);

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        if ($request->has('is_active')) {
            $data['is_active'] = $request->has('is_active');
        }

        $user->update($data);
        if ($avatarFile) {
            $avatars->replace($user->fresh(), $avatarFile);
        } elseif ($removeAvatar) {
            $avatars->remove($user->fresh());
        }

        if (array_key_exists('is_active', $data) && ! $user->is_active) {
            $user->tokens()->delete();
        }

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Ulanyjy maglumatlary täzelendi.',
                'data' => $user->fresh()->only(['id', 'name', 'email', 'phone', 'avatar_url', 'role', 'is_active']),
            ]);
        }

        return redirect()->route('admin.users.index')->with('success', 'Ulanyjy maglumatlary üstünlikli täzelendi.');
    }

    public function updateStatus(Request $request, User $user)
    {
        if ($request->user()->id === $user->id) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'Öz hasabyňyzyň ýagdaýyny üýtgedip bilmersiňiz.'], 422);
            }
            return back()->with('error', 'Öz hasabyňyzyň ýagdaýyny üýtgedip bilmersiňiz.');
        }

        $data = $request->validate(['is_active' => ['required', 'boolean']]);
        $user->update(['is_active' => $data['is_active']]);

        if (!$user->is_active) {
            $user->tokens()->delete();
        }

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Ulanyjynyň ýagdaýy täzelendi.',
                'data' => $user->only(['id', 'name', 'role', 'is_active']),
            ]);
        }

        return back()->with('success', 'Ulanyjynyň ýagdaýy üýtgedildi.');
    }


    public function updateClinicPermissions(Request $request, User $user)
    {
        abort_unless($user->role === 'receptionist', 422, 'Klinika rugsatlary diňe registrator üçin sazlanýar.');
        $data = $request->validate([
            'clinic_id' => ['required', 'integer', 'exists:clinics,id'],
            'permissions' => ['present', 'array'],
            'permissions.*' => ['nullable', 'string', Rule::in([
                'dashboard.view', 'appointments.view', 'appointments.create',
                'patients.view', 'doctors.view', 'payments.view', 'payments.manage',
            ])],
        ]);

        $permissions = array_values(array_unique(array_filter($data['permissions'], fn ($value) => filled($value))));
        $user->staffClinics()->syncWithoutDetaching([
            $data['clinic_id'] => ['permissions' => $permissions],
        ]);

        if (! $request->expectsJson() && ! $request->is('api/*')) {
            return back()->with('success', 'Registratoryň klinika rugsatlary täzelendi.');
        }

        return response()->json([
            'message' => 'Klinika boýunça rugsatlar täzelendi.',
            'data' => $user->staffClinics()->get(),
        ]);
    }

    public function restore(Request $request, int $id)
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $user->restore();

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json(['message' => 'Ulanyjy dikeldildi.', 'data' => $user]);
        }
        return back()->with('success', 'Ulanyjy dikeldildi.');
    }

    public function destroy(Request $request, User $user)
    {
        abort_if($request->user()->id === $user->id, 422, 'Öz hasabyňyzy pozup bilmersiňiz.');
        $user->tokens()->delete();
        $user->delete();

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json(['message' => 'Ulanyjy arhiwe geçirildi.']);
        }
        return back()->with('success', 'Ulanyjy arhiwe geçirildi.');
    }
}
