<?php

use App\Enums\RoleSlug;
use App\Models\Account;
use App\Models\Opportunity;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Artisan;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('archive command dry run reports closed opportunities without writing', function () {
    $owner = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    $account = Account::factory()->create(['owner_id' => $owner->id]);

    Opportunity::factory()->forAccount($account)->ownedBy($owner)->withStage('closed_won')->create([
        'close_date' => now()->subDays(400)->toDateString(),
        'archived_at' => null,
    ]);

    Artisan::call('crm:archive-old-records', ['--days' => 365, '--dry-run' => true]);

    expect(Opportunity::query()->whereNotNull('archived_at')->count())->toBe(0);
});

test('archive command archives old closed opportunities', function () {
    $owner = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    $account = Account::factory()->create(['owner_id' => $owner->id]);

    $old = Opportunity::factory()->forAccount($account)->ownedBy($owner)->withStage('closed_won')->create([
        'close_date' => now()->subDays(400)->toDateString(),
        'archived_at' => null,
    ]);

    $recent = Opportunity::factory()->forAccount($account)->ownedBy($owner)->withStage('closed_won')->create([
        'close_date' => now()->subDays(10)->toDateString(),
        'archived_at' => null,
    ]);

    Artisan::call('crm:archive-old-records', ['--days' => 365]);

    expect($old->fresh()->archived_at)->not->toBeNull()
        ->and($recent->fresh()->archived_at)->toBeNull();
});
