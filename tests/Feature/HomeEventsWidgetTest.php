<?php

use App\Enums\RoleSlug;
use App\Models\Event;
use App\Models\Task;
use App\Models\User;
use Database\Seeders\PicklistSeeder;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(PicklistSeeder::class);
});

test('home shows todays events and tasks widgets', function () {
    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    Event::factory()->ownedBy($user)->occurringToday()->create([
        'subject' => 'Morning Sync',
    ]);
    Task::factory()->ownedBy($user)->create([
        'subject' => 'Call prospect',
        'due_date' => now()->toDateString(),
        'status' => 'not_started',
    ]);

    $this->actingAs($user)
        ->get(route('home'))
        ->assertOk()
        ->assertSee('Morning Sync')
        ->assertSee('Call prospect')
        ->assertSee('tasks-heading', false)
        ->assertSee('events-heading', false);
});

test('home events widget empty state', function () {
    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();

    $this->actingAs($user)
        ->get(route('home'))
        ->assertOk()
        ->assertSee('No events scheduled for today')
        ->assertSee('No tasks due today');
});
