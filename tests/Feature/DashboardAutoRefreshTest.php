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

test('FR-DASH-003 refresh interval persists on create and update', function () {
    $user = User::factory()->withRole(RoleSlug::SystemAdministrator->value)->create();

    $this->actingAs($user)
        ->post(route('dashboards.store'), [
            'name' => 'Auto Board',
            'folder' => 'private',
            'is_private' => '1',
            'refresh_interval_minutes' => 10,
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

    $dashboard = Dashboard::query()->where('name', 'Auto Board')->first();
    expect($dashboard)->not->toBeNull()
        ->and($dashboard->refresh_interval_minutes)->toBe(10);

    $this->actingAs($user)
        ->put(route('dashboards.update', $dashboard), [
            'name' => 'Auto Board',
            'folder' => 'private',
            'is_private' => '1',
            'refresh_interval_minutes' => 30,
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
        ->assertRedirect(route('dashboards.show', $dashboard));

    expect($dashboard->fresh()->refresh_interval_minutes)->toBe(30);

    $this->actingAs($user)
        ->get(route('dashboards.show', $dashboard))
        ->assertOk()
        ->assertSee('Refresh')
        ->assertSee('Auto-refresh every 30 minutes');
});

test('FR-DASH-003 rejects invalid refresh interval', function () {
    $user = User::factory()->withRole(RoleSlug::SystemAdministrator->value)->create();

    $this->actingAs($user)
        ->post(route('dashboards.store'), [
            'name' => 'Bad Interval',
            'refresh_interval_minutes' => 15,
            'widgets' => [],
        ])
        ->assertSessionHasErrors('refresh_interval_minutes');
});

test('FR-DASH-003 refresh endpoint returns widget html when authorized', function () {
    $user = User::factory()->withRole(RoleSlug::SystemAdministrator->value)->create();
    $dashboard = Dashboard::factory()->ownedBy($user)->create([
        'refresh_interval_minutes' => 5,
    ]);

    $this->actingAs($user)
        ->getJson(route('dashboards.refresh', $dashboard))
        ->assertOk()
        ->assertJsonStructure(['html', 'refreshed_at']);
});

test('FR-DASH-003 refresh endpoint forbids unauthorized users', function () {
    $owner = User::factory()->withRole(RoleSlug::SystemAdministrator->value)->create();
    $stranger = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    $dashboard = Dashboard::factory()->ownedBy($owner)->create([
        'is_private' => true,
    ]);

    $this->actingAs($stranger)
        ->getJson(route('dashboards.refresh', $dashboard))
        ->assertForbidden();
});
