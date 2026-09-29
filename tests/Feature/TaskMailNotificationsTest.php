<?php

/**
 * Mail coverage lives in TasksTest (assignment, ownership, digest, overdue).
 * This file kept as an explicit mail suite entry point for Phase 4 Dev A.
 */

use App\Enums\RoleSlug;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskReminderNotification;
use Database\Seeders\PicklistSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(PicklistSeeder::class);
});

test('reminder command sends due reminders once', function () {
    Notification::fake();

    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    $task = Task::factory()->ownedBy($user)->create([
        'reminder_set' => true,
        'reminder_at' => now()->subMinute(),
        'reminder_sent_at' => null,
        'status' => 'not_started',
    ]);

    $this->artisan('crm:send-task-reminders')->assertSuccessful();

    Notification::assertSentTo($user, TaskReminderNotification::class);
    expect($task->fresh()->reminder_sent_at)->not->toBeNull();

    Notification::fake();
    $this->artisan('crm:send-task-reminders')->assertSuccessful();
    Notification::assertNothingSent();
});
