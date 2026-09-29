<?php

use App\Enums\RoleSlug;
use App\Enums\SharingAccessLevel;
use App\Models\Account;
use App\Models\RecordShare;
use App\Models\SharingDefault;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('system administrator can view any account regardless of owner', function () {
    $admin = User::factory()->withRole(RoleSlug::SystemAdministrator->value)->create();
    $owner = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    $account = Account::factory()->create(['owner_id' => $owner->id, 'name' => 'Admin Visible']);

    $this->actingAs($admin)
        ->get(route('accounts.show', $account))
        ->assertOk()
        ->assertSee('Admin Visible', false);
});

test('record share grants read access under private OWD', function () {
    $owner = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    $peer = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    $account = Account::factory()->create(['owner_id' => $owner->id, 'name' => 'Shared Account']);

    SharingDefault::query()->where('object_type', 'account')->update([
        'access_level' => SharingAccessLevel::Private,
    ]);

    $this->actingAs($peer)
        ->get(route('accounts.show', $account))
        ->assertForbidden();

    RecordShare::query()->create([
        'shareable_type' => 'account',
        'shareable_id' => $account->id,
        'user_id' => $peer->id,
        'access_level' => SharingAccessLevel::PublicReadOnly,
    ]);

    $this->actingAs($peer)
        ->get(route('accounts.show', $account))
        ->assertOk()
        ->assertSee('Shared Account', false);

    $this->actingAs($peer)
        ->put(route('accounts.update', $account), ['name' => 'Hacked'])
        ->assertForbidden();
});
