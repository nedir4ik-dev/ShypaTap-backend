<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Täze ulanyjy hasaba almak
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => $validated['password'],
            'role' => 'patient',
            'is_active' => true,
        ]);

        $token = $user->createToken('shypatap-token')->plainTextToken;

        return response()->json([
            'message' => 'Hasaba alyş üstünlikli tamamlandy.',
            'user' => $user->makeHidden(['password', 'remember_token']),
            'token' => $token,
        ], 201);
    }

    // Ulgama girmek
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Email ýa-da parol nädogry.',
            ], 401);
        }

        if (!$user->is_active) {
            return response()->json([
                'message' => 'Bu ulanyjy hasaby işjeň däl.',
            ], 403);
        }

        $token = $user->createToken('shypatap-token')->plainTextToken;

        return response()->json([
            'message' => 'Ulgama üstünlikli girdiňiz.',
            'user' => $user->makeHidden(['password', 'remember_token']),
            'token' => $token,
        ]);
    }

    // Häzirki ulanyjynyň maglumatlary
    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user()->makeHidden([
                'password',
                'remember_token',
            ]),
        ]);
    }

    // Ulgamdan çykmak
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Ulgamdan üstünlikli çykdyňyz.',
        ]);
    }

    public function updateProfile(Request $request)
    {
        $validated=$request->validate([
            'name'=>'sometimes|required|string|max:150',
            'phone'=>'sometimes|nullable|string|max:30',
            'email'=>'sometimes|required|email|max:255|unique:users,email,'.$request->user()->id,
        ]);
        $request->user()->update($validated);
        return response()->json(['message'=>'Profil täzelendi.','user'=>$request->user()->fresh()]);
    }

    public function changePassword(Request $request)
    {
        $v=$request->validate(['current_password'=>'required|string','password'=>'required|string|min:8|confirmed']);
        if(!Hash::check($v['current_password'],$request->user()->password)) {
            return response()->json(['message'=>'Häzirki parol nädogry.'],422);
        }
        $request->user()->update(['password'=>$v['password']]);
        $request->user()->tokens()->delete();
        return response()->json(['message'=>'Parol üýtgedildi. Täzeden ulgama giriň.']);
    }
}
