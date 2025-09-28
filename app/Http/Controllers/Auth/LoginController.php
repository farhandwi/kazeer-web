<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\Staff;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');

        // Cari staff berdasarkan email dan status aktif
        $staff = Staff::where('email', $credentials['email'])
                     ->where('is_active', true)
                     ->first();

        if (!$staff) {
            throw ValidationException::withMessages([
                'email' => ['These credentials do not match our records or account is inactive.'],
            ]);
        }

        // Attempt login
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            
            // Update last login
            $staff->update(['last_login_at' => now()]);

            // Redirect berdasarkan role
            return redirect()->intended($staff->redirectPath());
        }

        throw ValidationException::withMessages([
            'email' => ['These credentials do not match our records.'],
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}