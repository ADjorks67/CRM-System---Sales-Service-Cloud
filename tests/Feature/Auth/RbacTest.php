<?php

use App\Enums\RoleSlug;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('system administrator has users permissions', function () {
    $admin = User::factory()->withRole(RoleSlug::SystemAdministrator->value)->create();

    expect($admin->hasPermission('users.view'))->toBeTrue()
        ->and($admin->hasPermission('leads.create'))->toBeTrue();
});

test('read only user can view but not create leads', function () {
    $user = User::factory()->withRole(RoleSlug::ReadOnlyUser->value)->create();

    expect($user->hasPermission('leads.view'))->toBeTrue()
        ->and($user->hasPermission('leads.create'))->toBeFalse();
});

test('sales representative cannot manage users', function () {
    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();

    expect($user->hasPermission('users.view'))->toBeFalse()
        ->and($user->can('viewAny', User::class))->toBeFalse();

    $this->actingAs($user)
        ->get(route('users.index'))
        ->assertForbidden();
});

test('service representative can manage cases but not create opportunities', function () {
    $user = User::factory()->withRole(RoleSlug::ServiceRepresentative->value)->create();

    expect($user->hasPermission('cases.create'))->toBeTrue()
        ->and($user->hasPermission('opportunities.create'))->toBeFalse();
});
