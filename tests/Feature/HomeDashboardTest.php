<?php

use App\Enums\RoleSlug;
use App\Models\Account;
use App\Models\Opportunity;
use App\Models\User;
use App\Services\StageService;
use Database\Seeders\PicklistSeeder;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(PicklistSeeder::class);
});

test('home dashboard renders pipeline widgets for sales users', function () {
    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    $account = Account::factory()->ownedBy($user)->create();
    $opp = Opportunity::factory()->ownedBy($user)->create([
        'account_id' => $account->id,
        'amount' => 80000,
        'close_date' => now()->addMonth()->toDateString(),
        'lead_source' => 'web',
    ]);
    app(StageService::class)->changeStage($opp, 'negotiation_review', $user);

    $this->actingAs($user)
        ->get(route('home'))
        ->assertOk()
        ->assertSee('Pipeline Funnel')
        ->assertSee('Revenue by Source')
        ->assertSee('Key Deals')
        ->assertSee($opp->name);
});
