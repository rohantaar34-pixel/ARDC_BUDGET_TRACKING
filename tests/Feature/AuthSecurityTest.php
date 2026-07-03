<?php

use App\Models\LoginAttempt;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

uses(RefreshDatabase::class);

it('locks sign in for five minutes after ten failed attempts and resets afterward', function () {
    $user = User::factory()->create();

    for ($attempt = 1; $attempt <= 10; $attempt++) {
        $response = $this->from(route('login'))->post(route('login.post'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
    }

    expect(session('errors')->first('email'))->toContain('Too many login attempts.');

    $this->assertDatabaseHas('login_attempts', [
        'email' => strtolower($user->email),
        'attempts' => 10,
    ]);

    $response = $this->from(route('login'))->post(route('login.post'), [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $response->assertRedirect(route('login'));
    expect(session('errors')->first('email'))->toContain('Too many login attempts.');

    $this->travel(5)->minutes();
    $this->travel(1)->second();

    $response = $this->from(route('login'))->post(route('login.post'), [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $response->assertRedirect(route('login'));
    expect(session('errors')->first('email'))->toContain('9 attempt(s) remaining');

    $this->assertDatabaseHas('login_attempts', [
        'email' => strtolower($user->email),
        'attempts' => 1,
    ]);
});

it('clears login attempts after a successful sign in', function () {
    $user = User::factory()->create();

    LoginAttempt::create([
        'ip_address' => '127.0.0.1',
        'email' => strtolower($user->email),
        'attempts' => 4,
        'last_attempt_at' => now(),
    ]);

    $response = $this->post(route('login.post'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticatedAs($user);
    $this->assertDatabaseMissing('login_attempts', [
        'email' => strtolower($user->email),
    ]);
});

it('returns a generic forgot password response and only sends email for existing users', function () {
    Notification::fake();

    $user = User::factory()->create();

    $response = $this->from(route('password.request'))->post(route('password.email'), [
        'email' => $user->email,
    ]);

    $response->assertRedirect(route('password.request'));
    $response->assertSessionHas('status', 'If the email address exists in our records, a password reset link has been sent.');
    Notification::assertSentTo($user, ResetPasswordNotification::class);

    Notification::fake();

    $response = $this->from(route('password.request'))->post(route('password.email'), [
        'email' => 'missing@example.com',
    ]);

    $response->assertRedirect(route('password.request'));
    $response->assertSessionHas('status', 'If the email address exists in our records, a password reset link has been sent.');
    Notification::assertNothingSent();
});

it('resets the password and clears stored login attempts', function () {
    $user = User::factory()->create();

    LoginAttempt::create([
        'ip_address' => '127.0.0.1',
        'email' => strtolower($user->email),
        'attempts' => 10,
        'last_attempt_at' => now(),
        'lockout_until' => now()->addMinutes(5),
    ]);

    $token = Password::createToken($user);

    $response = $this->post(route('password.update'), [
        'token' => $token,
        'email' => $user->email,
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $response->assertRedirect(route('login'));
    $response->assertSessionHas('status');
    expect(Hash::check('new-password', $user->fresh()->password))->toBeTrue();
    $this->assertDatabaseMissing('login_attempts', [
        'email' => strtolower($user->email),
    ]);
});
