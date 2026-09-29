<?php

use App\Enums\RoleSlug;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('guests are redirected from home to login', function () {
    $this->get(route('home'))->assertRedirect(route('login'));
});

test('users can authenticate with valid credentials', function () {
    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create([
        'email' => 'rep@example.com',
        'password' => 'Password1!',
    ]);

    $this->post(route('login.store'), [
        'email' => 'rep@example.com',
        'password' => 'Password1!',
    ])->assertRedirect(route('home'));

    $this->assertAuthenticatedAs($user);
});

test('login fails with invalid password and increments attempts', function () {
    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create([
        'email' => 'rep@example.com',
        'password' => 'Password1!',
    ]);

    $this->from(route('login'))->post(route('login.store'), [
        'email' => 'rep@example.com',
        'password' => 'WrongPass1',
    ])->assertRedirect(route('login'))->assertSessionHasErrors('email');

    expect($user->fresh()->failed_login_attempts)->toBe(1);
});

test('account locks after five failed attempts', function () {
    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create([
        'email' => 'rep@example.com',
        'password' => 'Password1!',
    ]);

    for ($i = 0; $i < 5; $i++) {
        $this->from(route('login'))->post(route('login.store'), [
            'email' => 'rep@example.com',
            'password' => 'WrongPass1',
        ]);
    }

    $user->refresh();
    expect($user->isLocked())->toBeTrue();

    $this->from(route('login'))->post(route('login.store'), [
        'email' => 'rep@example.com',
        'password' => 'Password1!',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('remember me checkbox is accepted', function () {
    User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create([
        'email' => 'rep@example.com',
        'password' => 'Password1!',
    ]);

    $response = $this->post(route('login.store'), [
        'email' => 'rep@example.com',
        'password' => 'Password1!',
        'remember' => '1',
    ]);

    $response->assertRedirect(route('home'));
    $this->assertAuthenticated();
    expect($response->headers->getCookies())->not->toBeEmpty();
});

test('authenticated users are redirected away from login', function () {
    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();

    $this->actingAs($user)
        ->get(route('login'))
        ->assertRedirect(route('home'));
});

test('users can logout', function () {
    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();

    $this->actingAs($user)
        ->post(route('logout'))
        ->assertRedirect(route('login'));

    $this->assertGuest();
});

test('inactive users cannot sign in', function () {
    User::factory()->inactive()->withRole(RoleSlug::SalesRepresentative->value)->create([
        'email' => 'inactive@example.com',
        'password' => 'Password1!',
    ]);

    $this->from(route('login'))->post(route('login.store'), [
        'email' => 'inactive@example.com',
        'password' => 'Password1!',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});
