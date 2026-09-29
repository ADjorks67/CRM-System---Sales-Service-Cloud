<?php

/**
 * NFR-SEC-002 Optional email MFA.
 */

use App\Enums\RoleSlug;
use App\Models\User;
use App\Notifications\MfaCodeNotification;
use App\Services\MfaService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('mfa enabled user is challenged after password login', function () {
    Notification::fake();

    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create([
        'email' => 'mfa@example.com',
        'password' => 'Password1!',
        'mfa_enabled' => true,
    ]);

    $this->post(route('login.store'), [
        'email' => 'mfa@example.com',
        'password' => 'Password1!',
    ])->assertRedirect(route('mfa.challenge'));

    $this->assertGuest();
    Notification::assertSentTo($user, MfaCodeNotification::class);
});

test('valid otp completes login', function () {
    Notification::fake();

    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create([
        'email' => 'mfa2@example.com',
        'password' => 'Password1!',
        'mfa_enabled' => true,
    ]);

    $this->post(route('login.store'), [
        'email' => 'mfa2@example.com',
        'password' => 'Password1!',
    ]);

    $code = '123456';
    session([
        MfaService::SESSION_CODE_HASH => Hash::make($code),
        MfaService::SESSION_CODE_EXPIRES => now()->addMinutes(10)->timestamp,
        MfaService::SESSION_USER_ID => $user->id,
    ]);

    $this->post(route('mfa.challenge.store'), ['code' => $code])
        ->assertRedirect(route('home'));

    $this->assertAuthenticatedAs($user);
});

test('user can enable mfa and receive backup codes', function () {
    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();

    $response = $this->actingAs($user)
        ->post(route('mfa.enable'))
        ->assertRedirect(route('mfa.edit'));

    expect($user->fresh()->mfa_enabled)->toBeTrue();
    $response->assertSessionHas('mfa.plain_backup_codes');
});

test('admin can disable mfa for a locked out user', function () {
    $admin = User::factory()->withRole(RoleSlug::SystemAdministrator->value)->create();
    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create([
        'mfa_enabled' => true,
    ]);

    $this->actingAs($admin)
        ->post(route('users.mfa.disable', $user))
        ->assertRedirect(route('users.edit', $user));

    expect($user->fresh()->mfa_enabled)->toBeFalse();
});

test('backup code can complete mfa challenge once', function () {
    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create([
        'mfa_enabled' => true,
    ]);

    $mfa = app(MfaService::class);
    $codes = $mfa->generateBackupCodes($user, 1);

    session([
        MfaService::SESSION_USER_ID => $user->id,
        MfaService::SESSION_CODE_HASH => Hash::make('000000'),
        MfaService::SESSION_CODE_EXPIRES => now()->addMinutes(10)->timestamp,
    ]);

    $this->post(route('mfa.challenge.store'), ['code' => $codes[0]])
        ->assertRedirect(route('home'));

    $this->assertAuthenticatedAs($user);

    Auth::logout();
    session([
        MfaService::SESSION_USER_ID => $user->id,
        MfaService::SESSION_CODE_HASH => Hash::make('000000'),
        MfaService::SESSION_CODE_EXPIRES => now()->addMinutes(10)->timestamp,
    ]);

    $this->post(route('mfa.challenge.store'), ['code' => $codes[0]])
        ->assertSessionHasErrors('code');
});
