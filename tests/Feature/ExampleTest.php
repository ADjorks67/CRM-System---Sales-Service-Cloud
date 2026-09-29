<?php

use App\Enums\RoleSlug;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('the home page requires authentication', function () {
    $this->get(route('home'))->assertRedirect(route('login'));
});

test('authenticated users can view the home page', function () {
    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();

    $this->actingAs($user)
        ->get(route('home'))
        ->assertOk()
        ->assertSee('Home', false)
        ->assertSee('Leads', false)
        ->assertSee('Accounts', false);
});

test('chart and calendar blade components render registration hooks', function () {
    $html = view('components.chart', [
        'type' => 'donut',
        'config' => [
            'labels' => ['A', 'B'],
            'values' => [1, 2],
        ],
    ])->render();

    expect($html)->toContain('data-crm-chart="donut"');

    $calendar = view('components.calendar', [
        'eventsUrl' => '/events.json',
        'view' => 'timeGridWeek',
    ])->render();

    expect($calendar)->toContain('data-crm-calendar');
    expect($calendar)->toContain('data-crm-calendar-events="/events.json"');
    expect($calendar)->toContain('data-crm-calendar-view="timeGridWeek"');
});
