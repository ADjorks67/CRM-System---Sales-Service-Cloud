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

test('sales rep can create an event', function () {
    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();

    $this->actingAs($user)
        ->post(route('events.store'), [
            'subject' => 'Discovery Call',
            'starts_at' => now()->addDay()->setTime(10, 0)->format('Y-m-d H:i:s'),
            'ends_at' => now()->addDay()->setTime(11, 0)->format('Y-m-d H:i:s'),
            'show_as' => 'busy',
            'save_action' => 'save',
        ])
        ->assertRedirect();

    $event = Event::query()->where('subject', 'Discovery Call')->first();
    expect($event)->not->toBeNull()
        ->and($event->owner_id)->toBe($user->id)
        ->and($event->is_private)->toBeFalse();
});

test('calendar index and feed require authentication', function () {
    $this->get(route('calendar.index'))->assertRedirect(route('login'));
    $this->get(route('calendar.feed'))->assertRedirect(route('login'));
});

test('calendar feed returns visible events as fullcalendar json', function () {
    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    $other = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();

    $mine = Event::factory()->ownedBy($user)->occurringToday()->create(['subject' => 'My Meeting']);
    Event::factory()->ownedBy($other)->occurringToday()->create(['subject' => 'Other Public', 'is_private' => false]);
    Event::factory()->ownedBy($other)->occurringToday()->private()->create(['subject' => 'Other Private']);

    $response = $this->actingAs($user)
        ->getJson(route('calendar.feed', [
            'start' => now()->startOfDay()->toIso8601String(),
            'end' => now()->endOfDay()->toIso8601String(),
            'my_events' => 1,
            'public_team' => 1,
        ]));

    $response->assertOk();
    $titles = collect($response->json())->pluck('title');
    expect($titles)->toContain('My Meeting')
        ->and($titles)->not->toContain('Other Private');
});

test('private event is hidden from non-owners', function () {
    $owner = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    $other = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    $event = Event::factory()->ownedBy($owner)->private()->create();

    $this->actingAs($other)
        ->get(route('events.show', $event))
        ->assertForbidden();
});

test('drag reschedule updates event times', function () {
    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    $event = Event::factory()->ownedBy($user)->create();

    $start = now()->addDays(3)->setTime(14, 0);
    $end = $start->copy()->addHours(2);

    $this->actingAs($user)
        ->patchJson(route('events.reschedule', $event), [
            'starts_at' => $start->toIso8601String(),
            'ends_at' => $end->toIso8601String(),
            'is_all_day' => false,
        ])
        ->assertOk()
        ->assertJsonPath('ok', true);

    $event->refresh();
    expect($event->starts_at->equalTo($start))->toBeTrue()
        ->and($event->ends_at->equalTo($end))->toBeTrue();
});

test('end before start fails validation', function () {
    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();

    $this->actingAs($user)
        ->post(route('events.store'), [
            'subject' => 'Bad Times',
            'starts_at' => now()->addDay()->setTime(15, 0)->format('Y-m-d H:i:s'),
            'ends_at' => now()->addDay()->setTime(14, 0)->format('Y-m-d H:i:s'),
        ])
        ->assertSessionHasErrors(['ends_at']);
});

test('read only user cannot create events', function () {
    $user = User::factory()->withRole(RoleSlug::ReadOnlyUser->value)->create();

    $this->actingAs($user)
        ->post(route('events.store'), [
            'subject' => 'Nope',
            'starts_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'ends_at' => now()->addDay()->addHour()->format('Y-m-d H:i:s'),
        ])
        ->assertForbidden();
});
