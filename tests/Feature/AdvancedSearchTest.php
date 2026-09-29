<?php

/**
 * FR-SRCH-003 Advanced search + saved searches.
 */

use App\Enums\RoleSlug;
use App\Models\Account;
use App\Models\SavedSearch;
use App\Models\User;
use Database\Seeders\PicklistSeeder;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(PicklistSeeder::class);
});

test('advanced search finds records by field criteria with AND logic', function () {
    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    Account::factory()->ownedBy($user)->create(['name' => 'Alpha Advanced Co', 'industry' => 'technology']);
    Account::factory()->ownedBy($user)->create(['name' => 'Beta Other Co', 'industry' => 'finance']);

    $this->actingAs($user)
        ->post(route('search.advanced.run'), [
            'object_type' => 'account',
            'conditions' => [
                ['field' => 'name', 'operator' => 'contains', 'value' => 'Alpha', 'logic' => 'AND'],
                ['field' => 'industry', 'operator' => 'eq', 'value' => 'technology', 'logic' => 'AND'],
            ],
        ])
        ->assertOk()
        ->assertSee('Alpha Advanced Co')
        ->assertDontSee('Beta Other Co');
});

test('advanced search respects visibility scopes', function () {
    $owner = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    $other = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    Account::factory()->ownedBy($owner)->create(['name' => 'Hidden Private Search']);

    $this->actingAs($other)
        ->post(route('search.advanced.run'), [
            'object_type' => 'account',
            'conditions' => [
                ['field' => 'name', 'operator' => 'contains', 'value' => 'Hidden', 'logic' => 'AND'],
            ],
        ])
        ->assertOk()
        ->assertDontSee('Hidden Private Search');
});

test('user can save and rerun a search', function () {
    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    Account::factory()->ownedBy($user)->create(['name' => 'Saved Search Target']);

    $this->actingAs($user)
        ->post(route('search.advanced.save'), [
            'name' => 'My Alpha Search',
            'object_type' => 'account',
            'conditions' => [
                ['field' => 'name', 'operator' => 'contains', 'value' => 'Saved Search', 'logic' => 'AND'],
            ],
        ])
        ->assertRedirect();

    $saved = SavedSearch::query()->where('owner_id', $user->id)->first();
    expect($saved)->not->toBeNull();

    $this->actingAs($user)
        ->get(route('saved-searches.show', $saved))
        ->assertOk()
        ->assertSee('Saved Search Target');
});

test('saved search is owner-only', function () {
    $owner = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    $other = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    $saved = SavedSearch::factory()->ownedBy($owner)->create();

    $this->actingAs($other)
        ->get(route('saved-searches.show', $saved))
        ->assertForbidden();
});
