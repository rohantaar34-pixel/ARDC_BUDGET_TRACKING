<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('profile is available to every authenticated user without module access', function () {
    $user = User::factory()->create([
        'role' => 'employee',
        'module_permissions' => [],
    ]);

    $this->actingAs($user)
        ->get(route('profile.show'))
        ->assertOk()
        ->assertSee('Profile and security')
        ->assertSee('Change Password');
});

test('guests cannot access profile or change passwords', function () {
    $this->get(route('profile.show'))->assertRedirect(route('login'));

    $this->put(route('profile.password.update'), [
        'current_password' => 'OldPassword!123',
        'password' => 'NewPassword!456',
        'password_confirmation' => 'NewPassword!456',
    ])->assertRedirect(route('login'));
});

test('user can change their password with the correct current password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('OldPassword!123'),
    ]);

    $this->actingAs($user)
        ->put(route('profile.password.update'), [
            'current_password' => 'OldPassword!123',
            'password' => 'NewPassword!456',
            'password_confirmation' => 'NewPassword!456',
        ])
        ->assertRedirect(route('profile.show'))
        ->assertSessionHas('success');

    expect(Hash::check('NewPassword!456', $user->fresh()->password))->toBeTrue();
    $this->assertAuthenticatedAs($user);
});

test('password change rejects an incorrect current password and weak confirmation', function () {
    $user = User::factory()->create([
        'password' => Hash::make('OldPassword!123'),
    ]);

    $this->actingAs($user)
        ->from(route('profile.show'))
        ->put(route('profile.password.update'), [
            'current_password' => 'WrongPassword!123',
            'password' => 'weak',
            'password_confirmation' => 'different',
        ])
        ->assertRedirect(route('profile.show'))
        ->assertSessionHasErrors(['current_password', 'password']);

    expect(Hash::check('OldPassword!123', $user->fresh()->password))->toBeTrue();
});
