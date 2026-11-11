<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    // Login sahypasyny görkezmek
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // Login amalyny ýerine ýetirmek
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Roluna görä degişli sahypa ugratmak
            if ($user->role === 'admin') {
                return redirect()->intended('/admin/dashboard');
            } elseif ($user->role === 'doctor') {
                return redirect()->intended('/doctor/dashboard');
            }

            return redirect()->intended('/patient/dashboard');
        }

        return back()->withErrors([
            'email' => 'Girizilen email ýa-da parol ýalňyş.',
        ])->onlyInput('email');
    }

    // Ulgamdan çykmak (Logout)
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/admin/login');
    }
}
