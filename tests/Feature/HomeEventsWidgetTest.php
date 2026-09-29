<?php

use App\Enums\RoleSlug;
use App\Models\Event;
use App\Models\User;
use Database\Seeders\PicklistSeeder;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(PicklistSeeder::class);
});

test('home shows todays events widget', function () {
    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    Event::factory()->ownedBy($user)->occurringToday()->create([
        'subject' => 'Morning Sync',
    ]);

    $this->actingAs($user)
        ->get(route('home'))
        ->assertOk()
        ->assertSee('Morning Sync')
        ->assertSee('FR-HOME-004', false)
        ->assertSee('events-heading', false);
});

test('home events widget empty state', function () {
    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();

    $this->actingAs($user)
        ->get(route('home'))
        ->assertOk()
        ->assertSee('No events scheduled for today');
});
