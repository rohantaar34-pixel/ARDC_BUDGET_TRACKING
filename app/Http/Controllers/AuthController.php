<?php

namespace App\Http\Controllers;

use App\Models\LoginAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    private const MAX_ATTEMPTS = 10;

    private const LOCKOUT_MINUTES = 5;

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
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $attempt = $this->attemptRecord($request->ip(), $credentials['email']);
        $attempt = $this->resetWindowIfExpired($attempt);

        if ($this->isLockedOut($attempt)) {
            return $this->lockoutResponse($request, $attempt);
        }

        if (Auth::attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
        ], $request->boolean('remember'))) {
            $this->clearAttempts($credentials['email']);
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }

        $attempt = $this->recordFailedAttempt($attempt, $request->ip(), $credentials['email']);

        if ($this->isLockedOut($attempt)) {
            return $this->lockoutResponse($request, $attempt);
        }

        $remaining = self::MAX_ATTEMPTS - $attempt->attempts;

        return back()
            ->withInput($request->only('email', 'remember'))
            ->withErrors([
                'email' => "Invalid credentials. {$remaining} attempt(s) remaining before a 5-minute lockout.",
            ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function attemptRecord(string $ipAddress, string $email): LoginAttempt
    {
        return LoginAttempt::firstOrNew([
            'ip_address' => $ipAddress,
            'email' => $this->normalizeEmail($email),
        ]);
    }

    private function clearAttempts(string $email): void
    {
        LoginAttempt::where('email', $this->normalizeEmail($email))->delete();
    }

    private function resetWindowIfExpired(LoginAttempt $attempt): LoginAttempt
    {
        if (! $attempt->exists) {
            return $attempt;
        }

        $windowExpired = ($attempt->lockout_until && $attempt->lockout_until->isPast())
            || ($attempt->last_attempt_at && $attempt->last_attempt_at->lt(now()->subMinutes(self::LOCKOUT_MINUTES)));

        if (! $windowExpired) {
            return $attempt;
        }

        $attempt->forceFill([
            'attempts' => 0,
            'last_attempt_at' => null,
            'lockout_until' => null,
        ])->save();

        return $attempt->fresh();
    }

    private function isLockedOut(LoginAttempt $attempt): bool
    {
        return $attempt->exists && $attempt->lockout_until?->isFuture();
    }

    private function recordFailedAttempt(LoginAttempt $attempt, string $ipAddress, string $email): LoginAttempt
    {
        $attempt->ip_address = $ipAddress;
        $attempt->email = $this->normalizeEmail($email);
        $attempt->attempts = $attempt->exists ? $attempt->attempts + 1 : 1;
        $attempt->last_attempt_at = now();
        $attempt->lockout_until = $attempt->attempts >= self::MAX_ATTEMPTS
            ? now()->addMinutes(self::LOCKOUT_MINUTES)
            : null;
        $attempt->save();

        return $attempt->fresh();
    }

    private function lockoutResponse(Request $request, LoginAttempt $attempt)
    {
        $seconds = max(now()->diffInSeconds($attempt->lockout_until), 1);
        $minutes = intdiv($seconds, 60);
        $remainingSeconds = $seconds % 60;
        $parts = [];

        if ($minutes > 0) {
            $parts[] = "{$minutes} minute(s)";
        }

        if ($remainingSeconds > 0) {
            $parts[] = "{$remainingSeconds} second(s)";
        }

        if ($parts === []) {
            $parts[] = 'less than a minute';
        }

        return back()
            ->withInput($request->only('email', 'remember'))
            ->withErrors([
                'email' => 'Too many login attempts. Try again in ' . implode(' ', $parts) . '.',
            ]);
    }

    private function normalizeEmail(string $email): string
    {
        return Str::lower(trim($email));
    }
}
