<?php

use App\Enums\RoleSlug;
use App\Models\Account;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('guest login page has skip link labelled fields and csrf token', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertSee('Skip to main content', false)
        ->assertSee('id="main-content"', false)
        ->assertSee('for="email"', false)
        ->assertSee('for="password"', false)
        ->assertSee('name="_token"', false);
});

test('authenticated layout exposes primary nav landmark and skip link', function () {
    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();

    $this->actingAs($user)
        ->get(route('home'))
        ->assertOk()
        ->assertSee('Skip to main content', false)
        ->assertSee('aria-label="Primary"', false)
        ->assertSee('id="main-content"', false)
        ->assertSee('role="search"', false);
});

test('account create form fields are labelled for screen readers', function () {
    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();

    $this->actingAs($user)
        ->get(route('accounts.create'))
        ->assertOk()
        ->assertSee('for="name"', false)
        ->assertSee('<label', false);
});

test('destructive confirm patterns remain on account show', function () {
    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    $account = Account::factory()->ownedBy($user)->create();

    $this->actingAs($user)
        ->get(route('accounts.show', $account))
        ->assertOk()
        ->assertSee('confirm(', false);
});
