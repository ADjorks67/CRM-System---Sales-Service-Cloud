<?php

use App\Enums\RoleSlug;
use App\Models\Account;
use App\Models\User;
use Database\Seeders\PicklistSeeder;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(PicklistSeeder::class);
});

test('FR-ACCT-004 account show renders hierarchy tree and roll-ups', function () {
    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    $parent = Account::factory()->ownedBy($user)->create([
        'name' => 'Parent Co',
        'employees' => 10,
        'annual_revenue' => 1000,
    ]);
    $child = Account::factory()->ownedBy($user)->create([
        'name' => 'Child Co',
        'parent_account_id' => $parent->id,
        'employees' => 5,
        'annual_revenue' => 500,
    ]);

    $this->actingAs($user)
        ->get(route('accounts.show', $parent))
        ->assertOk()
        ->assertSee('Account Hierarchy')
        ->assertSee('Child Co')
        ->assertSee('15')
        ->assertSee('1,500.00')
        ->assertSee(route('accounts.hierarchy', $parent), false);

    $this->actingAs($user)
        ->get(route('accounts.hierarchy', $child))
        ->assertOk()
        ->assertSee('Parent Co')
        ->assertSee('Child Co');
});

test('FR-ACCT-004 rejects self-parent and descendant as parent', function () {
    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    $parent = Account::factory()->ownedBy($user)->create(['name' => 'Root']);
    $child = Account::factory()->ownedBy($user)->create([
        'name' => 'Leaf',
        'parent_account_id' => $parent->id,
    ]);

    $this->actingAs($user)
        ->put(route('accounts.update', $parent), [
            'name' => $parent->name,
            'parent_account_id' => $parent->id,
        ])
        ->assertSessionHasErrors('parent_account_id');

    $this->actingAs($user)
        ->put(route('accounts.update', $parent), [
            'name' => $parent->name,
            'parent_account_id' => $child->id,
        ])
        ->assertSessionHasErrors('parent_account_id');
});

test('FR-ACCT-004 invisible children are omitted from tree and roll-up', function () {
    $rep = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    $other = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();

    $parent = Account::factory()->ownedBy($rep)->create([
        'name' => 'Visible Parent',
        'employees' => 10,
        'annual_revenue' => 100,
    ]);
    Account::factory()->ownedBy($other)->create([
        'name' => 'Hidden Child',
        'parent_account_id' => $parent->id,
        'employees' => 999,
        'annual_revenue' => 99999,
    ]);
    Account::factory()->ownedBy($rep)->create([
        'name' => 'Visible Child',
        'parent_account_id' => $parent->id,
        'employees' => 3,
        'annual_revenue' => 50,
    ]);

    $this->actingAs($rep)
        ->get(route('accounts.show', $parent))
        ->assertOk()
        ->assertSee('Visible Child')
        ->assertDontSee('Hidden Child')
        ->assertSee('13')
        ->assertSee('150.00');
});
