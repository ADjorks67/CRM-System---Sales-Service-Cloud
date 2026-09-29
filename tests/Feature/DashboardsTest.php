<?php

use App\Enums\RoleSlug;
use App\Models\Dashboard;
use App\Models\User;
use Database\Seeders\PicklistSeeder;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(PicklistSeeder::class);
});

test('admin can create dashboard with prebuilt widget', function () {
    $user = User::factory()->withRole(RoleSlug::SystemAdministrator->value)->create();

    $this->actingAs($user)
        ->post(route('dashboards.store'), [
            'name' => 'Exec Overview',
            'folder' => 'private',
            'is_private' => '1',
            'widgets' => [
                [
                    'title' => 'Pipeline',
                    'widget_type' => 'table',
                    'source_type' => 'prebuilt',
                    'source_key' => 'all-pipeline-current-year',
                    'grid_x' => 0,
                    'grid_y' => 0,
                    'grid_w' => 6,
                    'grid_h' => 4,
                ],
            ],
        ])
        ->assertRedirect();

    $dashboard = Dashboard::query()->where('name', 'Exec Overview')->first();
    expect($dashboard)->not->toBeNull()
        ->and($dashboard->widgets()->count())->toBe(1);

    $this->actingAs($user)
        ->get(route('dashboards.show', $dashboard))
        ->assertOk()
        ->assertSee('Exec Overview')
        ->assertSee('Pipeline');
});

test('sales rep cannot create dashboards', function () {
    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();

    $this->actingAs($user)
        ->post(route('dashboards.store'), [
            'name' => 'Nope',
            'widgets' => [],
        ])
        ->assertForbidden();
});

test('dashboard filters persist in session', function () {
    $user = User::factory()->withRole(RoleSlug::SystemAdministrator->value)->create();
    $dashboard = Dashboard::factory()->ownedBy($user)->create();

    $this->actingAs($user)
        ->post(route('dashboards.filters.store'), [
            'date_from' => now()->startOfYear()->toDateString(),
            'date_to' => now()->endOfYear()->toDateString(),
            'owner_id' => $user->id,
            'redirect' => route('dashboards.show', $dashboard),
        ])
        ->assertRedirect(route('dashboards.show', $dashboard));

    $this->actingAs($user)
        ->get(route('dashboards.show', $dashboard))
        ->assertOk();
});

test('admin can create dashboard with multiple widgets', function () {
    $user = User::factory()->withRole(RoleSlug::SystemAdministrator->value)->create();

    $this->actingAs($user)
        ->post(route('dashboards.store'), [
            'name' => 'Multi Widget Board',
            'folder' => 'private',
            'is_private' => '1',
            'widgets' => [
                [
                    'title' => 'Pipeline',
                    'widget_type' => 'table',
                    'source_type' => 'prebuilt',
                    'source_key' => 'all-pipeline-current-year',
                    'grid_x' => 0,
                    'grid_y' => 0,
                    'grid_w' => 6,
                    'grid_h' => 4,
                ],
                [
                    'title' => 'Lead Sources',
                    'widget_type' => 'metric',
                    'source_type' => 'prebuilt',
                    'source_key' => 'leads-by-source-this-fy',
                    'grid_x' => 6,
                    'grid_y' => 0,
                    'grid_w' => 6,
                    'grid_h' => 4,
                ],
            ],
        ])
        ->assertRedirect();

    $dashboard = Dashboard::query()->where('name', 'Multi Widget Board')->first();
    expect($dashboard)->not->toBeNull()
        ->and($dashboard->widgets()->count())->toBe(2);

    $this->actingAs($user)
        ->get(route('dashboards.show', $dashboard))
        ->assertOk()
        ->assertSee('Pipeline')
        ->assertSee('Lead Sources');
});

test('admin can clone dashboard', function () {
    $user = User::factory()->withRole(RoleSlug::SystemAdministrator->value)->create();
    $dashboard = Dashboard::factory()->ownedBy($user)->create(['name' => 'Source Dash']);
    $dashboard->widgets()->create([
        'title' => 'Metric',
        'widget_type' => 'metric',
        'source_type' => 'prebuilt',
        'source_key' => 'leads-by-source-this-fy',
        'grid_x' => 0,
        'grid_y' => 0,
        'grid_w' => 4,
        'grid_h' => 3,
    ]);

    $this->actingAs($user)
        ->post(route('dashboards.clone', $dashboard))
        ->assertRedirect();

    expect(Dashboard::query()->where('name', 'Source Dash (Copy)')->exists())->toBeTrue();
});
