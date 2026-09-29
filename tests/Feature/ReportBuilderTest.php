<?php

use App\Enums\RoleSlug;
use App\Models\Lead;
use App\Models\SavedReport;
use App\Models\User;
use Database\Seeders\PicklistSeeder;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(PicklistSeeder::class);
});

test('admin can create and run a custom lead report', function () {
    $user = User::factory()->withRole(RoleSlug::SystemAdministrator->value)->create();
    Lead::factory()->ownedBy($user)->count(2)->create(['status' => 'new']);

    $this->actingAs($user)
        ->post(route('saved-reports.store'), [
            'name' => 'New Leads Custom',
            'report_type' => 'lead',
            'folder' => 'private',
            'is_private' => '1',
            'definition' => [
                'columns' => ['company', 'status', 'created_at'],
                'filters' => [
                    ['field' => 'status', 'operator' => 'eq', 'value' => 'new'],
                ],
                'group_by' => null,
            ],
        ])
        ->assertRedirect();

    $report = SavedReport::query()->where('name', 'New Leads Custom')->first();
    expect($report)->not->toBeNull();

    $this->actingAs($user)
        ->get(route('saved-reports.show', $report))
        ->assertOk()
        ->assertSee('New Leads Custom');
});

test('sales rep cannot create saved reports', function () {
    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();

    $this->actingAs($user)
        ->post(route('saved-reports.store'), [
            'name' => 'Nope',
            'report_type' => 'lead',
            'definition' => ['columns' => ['company']],
        ])
        ->assertForbidden();
});

test('owner can export saved report as csv', function () {
    $user = User::factory()->withRole(RoleSlug::SystemAdministrator->value)->create();
    Lead::factory()->ownedBy($user)->create(['company' => 'Export Co']);
    $report = SavedReport::factory()->ownedBy($user)->create([
        'name' => 'Export Me',
        'definition' => [
            'columns' => ['company', 'status'],
            'filters' => [],
            'group_by' => null,
            'chart' => null,
        ],
    ]);

    $this->actingAs($user)
        ->get(route('saved-reports.export', $report))
        ->assertOk()
        ->assertHeader('content-disposition');
});

test('sales rep can view saved report when public', function () {
    $admin = User::factory()->withRole(RoleSlug::SystemAdministrator->value)->create();
    $rep = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    $report = SavedReport::factory()->ownedBy($admin)->create([
        'is_private' => false,
        'folder' => 'public',
    ]);

    $this->actingAs($rep)
        ->get(route('saved-reports.show', $report))
        ->assertOk();
});
