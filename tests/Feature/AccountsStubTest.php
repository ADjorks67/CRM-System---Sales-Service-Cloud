<?php

use App\Enums\RoleSlug;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('guests cannot open accounts stub', function () {
    $this->get(route('accounts.index'))->assertRedirect(route('login'));
});

test('authenticated users see empty accounts module', function () {
    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();

    $this->actingAs($user)
        ->get(route('accounts.index'))
        ->assertOk()
        ->assertSee('Accounts', false)
        ->assertSee('No accounts yet', false)
        ->assertSee('Related Contacts', false);
});
