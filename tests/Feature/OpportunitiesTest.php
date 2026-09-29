<?php

use App\Enums\RoleSlug;
use App\Models\Account;
use App\Models\Opportunity;
use App\Models\OpportunityStageHistory;
use App\Models\User;
use Database\Seeders\PicklistSeeder;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(PicklistSeeder::class);
});

test('opportunity requires account', function () {
    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();

    $this->actingAs($user)
        ->post(route('opportunities.store'), [
            'name' => 'Missing Account Deal',
            'close_date' => now()->addWeek()->toDateString(),
            'stage' => 'qualification',
            'save_action' => 'save',
        ])
        ->assertSessionHasErrors(['account_id']);
});

test('sales rep can create opportunity with auto probability from stage', function () {
    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    $account = Account::factory()->ownedBy($user)->create();

    $this->actingAs($user)
        ->post(route('opportunities.store'), [
            'name' => 'Big Deal',
            'account_id' => $account->id,
            'amount' => '10000',
            'close_date' => now()->addMonth()->toDateString(),
            'stage' => 'qualification',
            'save_action' => 'save',
        ])
        ->assertRedirect();

    $opportunity = Opportunity::query()->where('name', 'Big Deal')->first();
    expect($opportunity)->not->toBeNull()
        ->and($opportunity->probability)->toBe(10)
        ->and($opportunity->expected_revenue)->toBe('1000.00')
        ->and($opportunity->owner_id)->toBe($user->id);

    expect(OpportunityStageHistory::query()->where('opportunity_id', $opportunity->id)->count())->toBe(1);
});

test('close date in the past fails on create', function () {
    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    $account = Account::factory()->ownedBy($user)->create();

    $this->actingAs($user)
        ->post(route('opportunities.store'), [
            'name' => 'Past Close',
            'account_id' => $account->id,
            'close_date' => now()->subDay()->toDateString(),
            'stage' => 'qualification',
            'save_action' => 'save',
        ])
        ->assertSessionHasErrors(['close_date']);
});

test('stage change writes history and updates probability', function () {
    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    $account = Account::factory()->ownedBy($user)->create();
    $opportunity = Opportunity::factory()->forAccount($account)->ownedBy($user)->withStage('qualification')->create([
        'amount' => 50000,
        'expected_revenue' => '5000.00',
    ]);

    OpportunityStageHistory::query()->create([
        'opportunity_id' => $opportunity->id,
        'from_stage' => null,
        'to_stage' => 'qualification',
        'probability' => 10,
        'changed_by' => $user->id,
    ]);

    $this->actingAs($user)
        ->post(route('opportunities.change-stage', $opportunity), [
            'stage' => 'proposal_price_quote',
        ])
        ->assertRedirect(route('opportunities.show', $opportunity));

    $opportunity->refresh();
    expect($opportunity->stage)->toBe('proposal_price_quote')
        ->and($opportunity->probability)->toBe(65)
        ->and($opportunity->expected_revenue)->toBe('32500.00');

    $this->assertDatabaseHas('opportunity_stage_histories', [
        'opportunity_id' => $opportunity->id,
        'from_stage' => 'qualification',
        'to_stage' => 'proposal_price_quote',
        'probability' => 65,
    ]);
});

test('opportunity can be cloned', function () {
    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    $account = Account::factory()->ownedBy($user)->create();
    $opportunity = Opportunity::factory()->forAccount($account)->ownedBy($user)->withStage('meeting_scheduled')->create([
        'name' => 'Source Opp',
        'amount' => 12000,
    ]);

    $this->actingAs($user)
        ->post(route('opportunities.clone.store', $opportunity), [
            'include_related' => '1',
        ])
        ->assertRedirect();

    $clone = Opportunity::query()->where('name', 'Source Opp (Copy)')->first();
    expect($clone)->not->toBeNull()
        ->and($clone->account_id)->toBe($account->id)
        ->and($clone->stage)->toBe('qualification')
        ->and($clone->probability)->toBe(10);
});

test('archive hides opportunity from default index', function () {
    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    $account = Account::factory()->ownedBy($user)->create();
    $opportunity = Opportunity::factory()->forAccount($account)->ownedBy($user)->create(['name' => 'To Archive']);

    $this->actingAs($user)
        ->post(route('opportunities.archive', $opportunity))
        ->assertRedirect(route('opportunities.index'));

    expect($opportunity->fresh()->archived_at)->not->toBeNull();

    $this->actingAs($user)
        ->get(route('opportunities.index'))
        ->assertOk()
        ->assertDontSee('To Archive');

    $this->actingAs($user)
        ->get(route('opportunities.index', ['view' => 'archived']))
        ->assertOk()
        ->assertSee('To Archive');
});
