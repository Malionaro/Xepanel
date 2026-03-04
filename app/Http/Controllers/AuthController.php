<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = \App\Models\FileUser::findByEmail($credentials['email']);

        if ($user && \Illuminate\Support\Facades\Hash::check($credentials['password'], $user->getAuthPassword())) {
            if ($user->two_factor_enabled) {
                $request->session()->put('2fa_user_id', $user->id);
                \App\Models\ActivityLog::log("2FA Challenge", "User: {$user->email}");
                return redirect()->route('two-factor.prompt');
            }

            Auth::login($user);
            $request->session()->regenerate();
            \App\Models\ActivityLog::log("User Logged In", "Method: Password");
            return redirect()->intended('dashboard');
        }

        \App\Models\ActivityLog::log("Failed Login Attempt", "Email: " . $credentials['email']);

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
