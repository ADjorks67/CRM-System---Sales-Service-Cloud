<?php

use App\Enums\RoleSlug;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('system administrator can open gdpr privacy tools', function () {
    $admin = User::factory()->withRole(RoleSlug::SystemAdministrator->value)->create();

    $this->actingAs($admin)
        ->get(route('gdpr.index'))
        ->assertOk()
        ->assertSee('aria-label="Breadcrumb"', false)
        ->assertSee('Anonymize personal data');
});

test('sales representative cannot open gdpr privacy tools', function () {
    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();

    $this->actingAs($user)
        ->get(route('gdpr.index'))
        ->assertForbidden();
});
