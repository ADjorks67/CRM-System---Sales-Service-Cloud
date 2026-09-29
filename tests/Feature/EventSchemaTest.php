<?php

use App\Enums\RoleSlug;
use App\Models\Account;
use App\Models\Event;
use App\Models\User;
use App\Services\EventQueryService;
use App\Services\OwnershipHistoryService;
use Database\Seeders\PicklistSeeder;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(PicklistSeeder::class);
});

test('event factory persists schema fields for Dev B calendar', function () {
    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    $account = Account::factory()->ownedBy($user)->create();

    $event = Event::factory()->ownedBy($user)->create([
        'subject' => 'Kickoff meeting',
        'related_type' => 'account',
        'related_id' => $account->id,
    ]);

    expect($event->fresh()->subject)->toBe('Kickoff meeting')
        ->and($event->related_type)->toBe('account')
        ->and($event->related_id)->toBe($account->id)
        ->and($event->starts_at)->not->toBeNull()
        ->and($event->ends_at)->not->toBeNull();
});

test('event query service returns today and upcoming events', function () {
    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();

    Event::factory()->ownedBy($user)->create([
        'subject' => 'Today event',
        'starts_at' => now()->setTime(10, 0),
        'ends_at' => now()->setTime(11, 0),
    ]);
    Event::factory()->ownedBy($user)->create([
        'subject' => 'Tomorrow event',
        'starts_at' => now()->addDay()->setTime(10, 0),
        'ends_at' => now()->addDay()->setTime(11, 0),
    ]);

    $service = app(EventQueryService::class);

    expect($service->todayForUser($user)->pluck('subject'))->toContain('Today event')
        ->and($service->upcomingForUser($user)->pluck('subject'))->toContain('Tomorrow event');
});

test('ownership transfer moves open events related to the record', function () {
    $owner = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    $newOwner = User::factory()->withRole(RoleSlug::SalesManager->value)->create();
    $account = Account::factory()->ownedBy($owner)->create();

    $event = Event::factory()->ownedBy($owner)->create([
        'subject' => 'Transfer me',
        'related_type' => 'account',
        'related_id' => $account->id,
        'starts_at' => now()->addDay(),
        'ends_at' => now()->addDay()->addHour(),
    ]);

    app(OwnershipHistoryService::class)->changeOwner($account, $newOwner, $owner, [
        'transfer_open_activities' => true,
    ]);

    expect($event->fresh()->owner_id)->toBe($newOwner->id);
});
