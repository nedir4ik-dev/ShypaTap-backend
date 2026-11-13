<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()->select([
            'id',
            'name',
            'email',
            'phone',
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
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%")
                    ->orWhere('phone', 'ilike', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(15);

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

        return view('admin.users.create');
    }

    public function show(Request $request, User $user)
    {
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'data' => $user->only(['id', 'name', 'email', 'phone', 'role', 'is_active', 'created_at']),
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

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', Rule::in(['patient', 'doctor', 'receptionist', 'admin'])],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $data['is_active'] = $request->has('is_active');
        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Ulanyjy döredildi.',
                'data' => $user->only(['id', 'name', 'email', 'phone', 'role', 'is_active']),
            ], 201);
        }

        return redirect()->route('admin.users.index')->with('success', 'Ulanyjy üstünlikli döredildi.');
    }

    public function update(Request $request, User $user)
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
        ]);

        if ($actor->id === $user->id && array_key_exists('is_active', $data) && !$data['is_active']) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'Öz hasabyňyzy öçürip bilmersiňiz.'], 422);
            }
            return back()->withErrors(['is_active' => 'Öz hasabyňyzy öçürip bilmersiňiz.']);
        }

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        if ($request->has('is_active')) {
            $data['is_active'] = $request->has('is_active');
        }

        $user->update($data);

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Ulanyjy maglumatlary täzelendi.',
                'data' => $user->fresh()->only(['id', 'name', 'email', 'phone', 'role', 'is_active']),
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

    public function destroy(Request $request, User $user)
    {
        abort_if($request->user()->id===$user->id,422,'Öz hasabyňyzy pozup bilmersiňiz.');
        $user->tokens()->delete();
        $user->delete();
        return response()->json(['message'=>'Ulanyjy hasaby ýumşak pozuldy.']);
    }
}
