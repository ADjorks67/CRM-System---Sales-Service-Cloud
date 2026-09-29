<?php

/**
 * FR-RPT-006 Report subscriptions.
 */

use App\Enums\RoleSlug;
use App\Models\ReportSubscription;
use App\Models\SavedReport;
use App\Models\User;
use App\Notifications\ReportSubscriptionNotification;
use Database\Seeders\PicklistSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(PicklistSeeder::class);
});

test('user can subscribe to a visible saved report', function () {
    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    $report = SavedReport::factory()->ownedBy($user)->create(['name' => 'Sub Report']);

    $this->actingAs($user)
        ->post(route('subscriptions.store', $report), [
            'frequency' => 'daily',
            'time_of_day' => '08:30',
        ])
        ->assertRedirect(route('subscriptions.index'));

    $this->assertDatabaseHas('report_subscriptions', [
        'saved_report_id' => $report->id,
        'user_id' => $user->id,
        'frequency' => 'daily',
    ]);
});

test('subscription command emails csv attachment', function () {
    Notification::fake();

    $user = User::factory()->withRole(RoleSlug::SalesManager->value)->create();
    $report = SavedReport::factory()->ownedBy($user)->create();
    $subscription = ReportSubscription::factory()
        ->forUser($user)
        ->forReport($report)
        ->create([
            'frequency' => 'daily',
            'time_of_day' => '07:00:00',
            'next_run_at' => now()->subMinute(),
        ]);

    $this->artisan('crm:send-report-subscriptions')->assertSuccessful();

    Notification::assertSentTo($user, ReportSubscriptionNotification::class);
    expect($subscription->fresh()->last_sent_at)->not->toBeNull();
    expect($subscription->fresh()->next_run_at)->toBeGreaterThan(now());
});

test('user cannot subscribe to a report they cannot view', function () {
    $owner = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    $other = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    $report = SavedReport::factory()->ownedBy($owner)->create(['is_private' => true]);

    $this->actingAs($other)
        ->post(route('subscriptions.store', $report), [
            'frequency' => 'daily',
            'time_of_day' => '08:00',
        ])
        ->assertForbidden();
});
