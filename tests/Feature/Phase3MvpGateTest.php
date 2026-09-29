<?php

use App\Enums\RoleSlug;
use App\Models\Account;
use App\Models\CrmCase;
use App\Models\Opportunity;
use App\Models\User;
use Database\Seeders\PicklistSeeder;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(PicklistSeeder::class);
});

test('phase 3 mvp gate covers cases opportunities search reports and home', function () {
    $admin = User::factory()->withRole(RoleSlug::SystemAdministrator->value)->create();
    $account = Account::factory()->ownedBy($admin)->create(['name' => 'MVP Account']);
    $opportunity = Opportunity::factory()->ownedBy($admin)->create([
        'name' => 'MVP Opportunity',
        'account_id' => $account->id,
        'close_date' => now()->addMonth()->toDateString(),
    ]);
    $case = CrmCase::factory()->ownedBy($admin)->create([
        'subject' => 'MVP Case',
        'account_id' => $account->id,
    ]);

    $this->actingAs($admin)
        ->get(route('opportunities.index'))
        ->assertOk()
        ->assertSee('MVP Opportunity');

    $this->actingAs($admin)
        ->get(route('cases.index'))
        ->assertOk()
        ->assertSee($case->case_number);

    $this->actingAs($admin)
        ->get(route('search.index', ['q' => 'MVP']))
        ->assertOk()
        ->assertSee('MVP Account');

    $this->actingAs($admin)
        ->get(route('reports.index'))
        ->assertOk();

    $this->actingAs($admin)
        ->get(route('home'))
        ->assertOk()
        ->assertSee('Pipeline Funnel');

    $readonly = User::factory()->withRole(RoleSlug::ReadOnlyUser->value)->create();

    $this->actingAs($readonly)
        ->get(route('opportunities.create'))
        ->assertForbidden();

    $this->actingAs($readonly)
        ->get(route('cases.create'))
        ->assertForbidden();
});
