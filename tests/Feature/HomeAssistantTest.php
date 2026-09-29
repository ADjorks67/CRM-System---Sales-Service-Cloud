<?php

use App\Enums\RoleSlug;
use App\Models\Account;
use App\Models\AssistantDismissal;
use App\Models\Opportunity;
use App\Models\Task;
use App\Models\User;
use Database\Seeders\PicklistSeeder;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(PicklistSeeder::class);
});

test('FR-HOME-007 shows inactive account and stale closing opportunity recommendations', function () {
    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();

    $inactive = Account::factory()->ownedBy($user)->create([
        'name' => 'Quiet Account Co',
        'created_at' => now()->subDays(60),
        'updated_at' => now()->subDays(60),
    ]);

    $active = Account::factory()->ownedBy($user)->create(['name' => 'Busy Account Co']);
    Task::factory()->ownedBy($user)->create([
        'related_type' => 'account',
        'related_id' => $active->id,
        'subject' => 'Recent call',
    ]);

    $staleOpp = Opportunity::factory()->ownedBy($user)->forAccount($inactive)->create([
        'name' => 'Stale Closing Deal',
        'close_date' => now()->addDays(3)->toDateString(),
        'is_closed' => false,
        'amount' => 1000,
        'probability' => 10,
    ]);
    $staleOpp->forceFill(['updated_at' => now()->subDays(20)])->saveQuietly();

    Opportunity::factory()->ownedBy($user)->forAccount($active)->create([
        'name' => 'Fresh Deal',
        'close_date' => now()->addDays(3)->toDateString(),
        'is_closed' => false,
        'amount' => 1000,
        'probability' => 10,
        'updated_at' => now()->subDays(2),
    ]);

    $this->actingAs($user)
        ->get(route('home'))
        ->assertOk()
        ->assertSee('Quiet Account Co')
        ->assertSee('No Task or Event activity')
        ->assertSee('Stale Closing Deal')
        ->assertDontSee('Busy Account Co')
        ->assertDontSee('Fresh Deal');
});

test('FR-HOME-007 dismiss hides recommendation for user', function () {
    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    $account = Account::factory()->ownedBy($user)->create([
        'name' => 'Dismiss Me Account',
        'created_at' => now()->subDays(45),
        'updated_at' => now()->subDays(45),
    ]);

    $key = 'inactive_account:'.$account->id;

    $this->actingAs($user)
        ->post(route('home.assistant.dismiss'), ['recommendation_key' => $key])
        ->assertRedirect(route('home'));

    expect(AssistantDismissal::query()->where('user_id', $user->id)->where('recommendation_key', $key)->exists())->toBeTrue();

    $this->actingAs($user)
        ->get(route('home'))
        ->assertOk()
        ->assertDontSee('Dismiss Me Account');
});

test('FR-HOME-007 respects visibleTo for inactive accounts', function () {
    $rep = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    $other = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();

    Account::factory()->ownedBy($other)->create([
        'name' => 'Other Quiet Corp',
        'created_at' => now()->subDays(40),
        'updated_at' => now()->subDays(40),
    ]);

    $this->actingAs($rep)
        ->get(route('home'))
        ->assertOk()
        ->assertDontSee('Other Quiet Corp');
});
