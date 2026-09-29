<?php

use App\Enums\RoleSlug;
use App\Models\User;
use App\Services\PasswordHistoryService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('password reset link can be requested', function () {
    Notification::fake();

    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create([
        'email' => 'rep@example.com',
    ]);

    $this->post(route('password.email'), ['email' => 'rep@example.com'])
        ->assertSessionHas('success');

    Notification::assertSentTo($user, ResetPassword::class);
});

test('password can be reset with valid token', function () {
    Notification::fake();

    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create([
        'email' => 'rep@example.com',
        'password' => 'Password1!',
    ]);

    $token = Password::createToken($user);

    $this->post(route('password.update'), [
        'token' => $token,
        'email' => 'rep@example.com',
        'password' => 'Password2!',
        'password_confirmation' => 'Password2!',
    ])->assertRedirect(route('login'));

    expect(Hash::check('Password2!', $user->fresh()->password))->toBeTrue();
});

test('password reset rejects reuse of last five passwords', function () {
    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create([
        'email' => 'rep@example.com',
        'password' => 'Password1!',
    ]);

    app(PasswordHistoryService::class)->store($user, 'Password1!');

    $token = Password::createToken($user);

    $this->from(route('password.reset', ['token' => $token]))
        ->post(route('password.update'), [
            'token' => $token,
            'email' => 'rep@example.com',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
        ])
        ->assertSessionHasErrors('password');
});

test('password complexity is enforced on reset', function () {
    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create([
        'email' => 'rep@example.com',
    ]);

    $token = Password::createToken($user);

    $this->post(route('password.update'), [
        'token' => $token,
        'email' => 'rep@example.com',
        'password' => 'short',
        'password_confirmation' => 'short',
    ])->assertSessionHasErrors('password');
});
