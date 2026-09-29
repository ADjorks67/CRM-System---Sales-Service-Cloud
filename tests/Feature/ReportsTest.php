<?php

use App\Enums\RoleSlug;
use App\Models\Account;
use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\User;
use Database\Seeders\PicklistSeeder;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(PicklistSeeder::class);
});

test('reports catalog is available to users with reports.view', function () {
    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();

    $this->actingAs($user)
        ->get(route('reports.index'))
        ->assertOk()
        ->assertSee('Leads by Source This FY')
        ->assertSee('All Pipeline');
});

test('prebuilt lead report runs with drill down rows', function () {
    $user = User::factory()->withRole(RoleSlug::SalesManager->value)->create();
    Lead::factory()->ownedBy($user)->create([
        'company' => 'Report Co',
        'lead_source' => 'web',
    ]);

    $this->actingAs($user)
        ->get(route('reports.show', 'leads-by-source-this-fy'))
        ->assertOk()
        ->assertSee('Lead Source')
        ->assertSee('Web');
});

test('pipeline report lists open opportunities', function () {
    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    $account = Account::factory()->ownedBy($user)->create(['name' => 'Pipeline Account']);
    Opportunity::factory()->ownedBy($user)->create([
        'name' => 'Open Pipeline Deal',
        'account_id' => $account->id,
        'amount' => 10000,
        'close_date' => now()->addDays(20)->toDateString(),
        'is_closed' => false,
    ]);

    $this->actingAs($user)
        ->get(route('reports.show', 'all-pipeline-current-year'))
        ->assertOk()
        ->assertSee('Open Pipeline Deal');
});
