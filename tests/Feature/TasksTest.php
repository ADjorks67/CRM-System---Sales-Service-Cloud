<?php

use App\Enums\RoleSlug;
use App\Models\Account;
use App\Models\Task;
use App\Models\User;
use App\Notifications\OwnershipChangedNotification;
use App\Notifications\TaskAssignedNotification;
use App\Notifications\TaskDigestNotification;
use App\Notifications\TaskOverdueNotification;
use App\Services\TaskQueryService;
use Database\Seeders\PicklistSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(PicklistSeeder::class);
});

test('user can create a task with required subject', function () {
    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    $account = Account::factory()->ownedBy($user)->create();

    $this->actingAs($user)
        ->post(route('tasks.store'), [
            'subject' => 'Follow up call',
            'status' => 'not_started',
            'priority' => 'high',
            'related_type' => 'account',
            'related_id' => $account->id,
            'due_date' => now()->addDay()->toDateString(),
            'save_action' => 'save',
        ])
        ->assertRedirect();

    $task = Task::query()->where('subject', 'Follow up call')->first();
    expect($task)->not->toBeNull()
        ->and($task->owner_id)->toBe($user->id)
        ->and($task->related_type)->toBe('account')
        ->and($task->related_id)->toBe($account->id);
});

test('task list filters open today overdue and completed', function () {
    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    Task::factory()->ownedBy($user)->dueToday()->create(['subject' => 'Due today task']);
    Task::factory()->ownedBy($user)->overdue()->create(['subject' => 'Overdue task']);
    Task::factory()->ownedBy($user)->completed()->create(['subject' => 'Done task']);

    $this->actingAs($user)
        ->get(route('tasks.index', ['view' => 'today']))
        ->assertOk()
        ->assertSee('Due today task')
        ->assertDontSee('Overdue task');

    $this->actingAs($user)
        ->get(route('tasks.index', ['view' => 'overdue']))
        ->assertOk()
        ->assertSee('Overdue task');

    $this->actingAs($user)
        ->get(route('tasks.index', ['view' => 'completed']))
        ->assertOk()
        ->assertSee('Done task');
});

test('task can be marked complete inline', function () {
    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    $task = Task::factory()->ownedBy($user)->create(['status' => 'not_started']);

    $this->actingAs($user)
        ->post(route('tasks.complete', $task))
        ->assertRedirect();

    expect($task->fresh()->status)->toBe('completed')
        ->and($task->fresh()->completed_at)->not->toBeNull();
});

test('assigning task to another user sends notification', function () {
    Notification::fake();

    $actor = User::factory()->withRole(RoleSlug::SalesManager->value)->create();
    $assignee = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();

    $this->actingAs($actor)
        ->post(route('tasks.store'), [
            'subject' => 'Assigned task',
            'status' => 'not_started',
            'priority' => 'normal',
            'owner_id' => $assignee->id,
            'save_action' => 'save',
        ])
        ->assertRedirect();

    Notification::assertSentTo($assignee, TaskAssignedNotification::class);
});

test('owner change with notify sends ownership notification', function () {
    Notification::fake();

    $owner = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    $newOwner = User::factory()->withRole(RoleSlug::SalesManager->value)->create();
    $account = Account::factory()->ownedBy($owner)->create();

    $this->actingAs($owner)
        ->post(route('accounts.change-owner', $account), [
            'owner_id' => $newOwner->id,
            'notify_new_owner' => '1',
        ])
        ->assertRedirect();

    Notification::assertSentTo($newOwner, OwnershipChangedNotification::class);
});

test('digest and overdue commands notify task owners', function () {
    Notification::fake();

    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    Task::factory()->ownedBy($user)->dueToday()->create();
    Task::factory()->ownedBy($user)->overdue()->create();

    $this->artisan('crm:send-task-digests')->assertSuccessful();
    $this->artisan('crm:send-overdue-task-notifications')->assertSuccessful();

    Notification::assertSentTo($user, TaskDigestNotification::class);
    Notification::assertSentTo($user, TaskOverdueNotification::class);
});

test('task query service returns due today for home widgets', function () {
    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    Task::factory()->ownedBy($user)->dueToday()->create(['subject' => 'Widget due']);
    Task::factory()->ownedBy($user)->overdue()->create(['subject' => 'Widget overdue']);

    $service = app(TaskQueryService::class);

    expect($service->dueToday($user)->pluck('subject'))->toContain('Widget due')
        ->and($service->overdue($user)->pluck('subject'))->toContain('Widget overdue');
});
