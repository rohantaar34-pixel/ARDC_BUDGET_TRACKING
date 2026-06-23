<?php

namespace App\Http\Controllers;

use App\Models\BannedIp;
use App\Models\LoginAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    private const MAX_ATTEMPTS = 5;

    public function showLogin()
    {
        if (Auth::check()) {
            if (Auth::user()->isEmployee()) {
                return redirect()->route('monitoring.submit');
            }

            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $ip = $request->ip();

        if (BannedIp::where('ip_address', $ip)->exists()) {
            return back()->withErrors(['email' => 'Your IP has been banned. Contact the administrator.']);
        }

        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            LoginAttempt::where('ip_address', $ip)->delete();
            $request->session()->regenerate();

            if (Auth::user()->isEmployee()) {
                return redirect()->route('monitoring.submit');
            }

            return redirect()->intended(route('dashboard'));
        }

        $record = LoginAttempt::firstOrCreate(['ip_address' => $ip], ['attempts' => 0]);
        $record->increment('attempts');
        $attempts = $record->fresh()->attempts;

        if ($attempts >= self::MAX_ATTEMPTS) {
            BannedIp::create([
                'ip_address'    => $ip,
                'attempt_count' => $attempts,
                'reason'        => 'Too many failed login attempts',
                'banned_at'     => now(),
            ]);
            LoginAttempt::where('ip_address', $ip)->delete();
            return back()->withErrors(['email' => 'Your IP has been banned after ' . self::MAX_ATTEMPTS . ' failed attempts. Contact the administrator.']);
        }

        $remaining = self::MAX_ATTEMPTS - $attempts;

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => "Invalid credentials. {$remaining} attempt(s) left before your IP is banned."]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
