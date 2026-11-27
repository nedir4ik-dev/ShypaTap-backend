<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AvatarService;
use App\Services\RoleAssignmentService;
use App\Http\Requests\Auth\RegisterRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function register(RegisterRequest $request, RoleAssignmentService $roles)
    {
        $validated = $request->validated();

        // Public registration is intentionally restricted to Patient accounts.
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => $validated['password'],
            'role' => 'patient',
            'is_active' => true,
        ]);

        $roles->assignDefault($user);
        $token = $user->createToken('shypatap-token')->plainTextToken;

        return response()->json([
            'message' => 'Hasaba alyş üstünlikli tamamlandy. Ulgama girmek üçin hasabyňyza giriň.',
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return response()->json(['message' => 'Email ýa-da parol nädogry.'], 401);
        }

        if (! $user->is_active) {
            return response()->json(['message' => 'Bu ulanyjy hasaby işjeň däl.'], 403);
        }

        $token = $user->createToken('shypatap-token')->plainTextToken;

        return response()->json([
            'message' => 'Ulgama üstünlikli girdiňiz.',
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function me(Request $request)
    {
        return response()->json(['user' => $request->user()]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['message' => 'Ulgamdan üstünlikli çykdyňyz.']);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:150'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:30'],
            'date_of_birth' => ['sometimes', 'nullable', 'date', 'before:today'],
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'Profil täzelendi.',
            'user' => $user->fresh(),
        ]);
    }

    public function uploadAvatar(Request $request, AvatarService $avatars)
    {
        $request->validate([
            'avatar' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png', 'max:3072'],
        ]);

        $avatars->replace($request->user(), $request->file('avatar'));

        return response()->json([
            'message' => 'Profil suraty täzelendi.',
            'user' => $request->user()->fresh(),
        ]);
    }

    public function deleteAvatar(Request $request, AvatarService $avatars)
    {
        $avatars->remove($request->user());

        return response()->json([
            'message' => 'Profil suraty aýryldy.',
            'user' => $request->user()->fresh(),
        ]);
    }

    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (! Hash::check($validated['current_password'], $request->user()->password)) {
            return response()->json(['message' => 'Häzirki parol nädogry.'], 422);
        }

        $request->user()->update(['password' => $validated['password']]);
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Parol üýtgedildi. Täzeden ulgama giriň.']);
    }
}
