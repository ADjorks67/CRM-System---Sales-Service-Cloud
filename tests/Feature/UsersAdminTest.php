<?php

use App\Enums\RoleSlug;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('system administrator can view users index', function () {
    $admin = User::factory()->withRole(RoleSlug::SystemAdministrator->value)->create();

    $this->actingAs($admin)
        ->get(route('users.index'))
        ->assertOk()
        ->assertSee('Users', false);
});

test('system administrator can create a user', function () {
    $admin = User::factory()->withRole(RoleSlug::SystemAdministrator->value)->create();
    $role = Role::query()->where('slug', RoleSlug::SalesRepresentative->value)->firstOrFail();

    $this->actingAs($admin)
        ->post(route('users.store'), [
            'name' => 'New Rep',
            'email' => 'new.rep@example.com',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'role_id' => $role->id,
            'is_active' => '1',
        ])
        ->assertRedirect(route('users.index'));

    $this->assertDatabaseHas('users', [
        'email' => 'new.rep@example.com',
        'role_id' => $role->id,
    ]);
});

test('non admin cannot create users', function () {
    $manager = User::factory()->withRole(RoleSlug::SalesManager->value)->create();
    $role = Role::query()->where('slug', RoleSlug::SalesRepresentative->value)->firstOrFail();

    $this->actingAs($manager)
        ->post(route('users.store'), [
            'name' => 'Blocked',
            'email' => 'blocked@example.com',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'role_id' => $role->id,
        ])
        ->assertForbidden();
});
