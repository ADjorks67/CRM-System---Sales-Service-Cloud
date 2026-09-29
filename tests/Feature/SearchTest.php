<?php

use App\Enums\RoleSlug;
use App\Models\Account;
use App\Models\CrmCase;
use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\User;
use Database\Seeders\PicklistSeeder;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(PicklistSeeder::class);
});

test('global search suggestions return top matches per object', function () {
    $user = User::factory()->withRole(RoleSlug::SystemAdministrator->value)->create();
    $account = Account::factory()->ownedBy($user)->create(['name' => 'Zephyr Search Co']);
    Lead::factory()->ownedBy($user)->create(['company' => 'Zephyr Leads LLC', 'last_name' => 'Zed']);
    Opportunity::factory()->ownedBy($user)->create([
        'name' => 'Zephyr Deal',
        'account_id' => $account->id,
        'close_date' => now()->addMonth()->toDateString(),
    ]);
    CrmCase::factory()->ownedBy($user)->create(['subject' => 'Zephyr outage']);

    $this->actingAs($user)
        ->getJson(route('search.suggest', ['q' => 'Zeph']))
        ->assertOk()
        ->assertJsonStructure(['groups' => ['accounts', 'leads', 'opportunities', 'cases']]);
});

test('search results page finds records and remembers query', function () {
    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    Account::factory()->ownedBy($user)->create(['name' => 'Alpha Search Account']);

    $this->actingAs($user)
        ->get(route('search.index', ['q' => 'Alpha']))
        ->assertOk()
        ->assertSee('Alpha Search Account');

    $this->assertDatabaseHas('recent_searches', [
        'user_id' => $user->id,
        'query' => 'Alpha',
    ]);
});

test('private records are hidden from other users in search', function () {
    $owner = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    $other = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    Account::factory()->ownedBy($owner)->create(['name' => 'Secret Private Corp']);

    $this->actingAs($other)
        ->get(route('search.index', ['q' => 'Secret']))
        ->assertOk()
        ->assertDontSee('Secret Private Corp');
});
