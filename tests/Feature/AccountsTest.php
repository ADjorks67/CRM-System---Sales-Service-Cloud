<?php

use App\Enums\RoleSlug;
use App\Models\Account;
use App\Models\OwnershipHistory;
use App\Models\User;
use Database\Seeders\PicklistSeeder;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(PicklistSeeder::class);
});

test('guests cannot open accounts index', function () {
    $this->get(route('accounts.index'))->assertRedirect(route('login'));
});

test('sales rep can create account with billing copied to shipping', function () {
    $rep = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();

    $this->actingAs($rep)
        ->post(route('accounts.store'), [
            'name' => 'Contoso Ltd',
            'type' => 'customer',
            'industry' => 'technology',
            'billing_street' => '1 Main St',
            'billing_city' => 'Seattle',
            'billing_state' => 'WA',
            'billing_postal_code' => '98101',
            'billing_country' => 'USA',
            'copy_billing_to_shipping' => '1',
            'save_action' => 'save',
        ])
        ->assertRedirect();

    $account = Account::query()->where('name', 'Contoso Ltd')->first();

    expect($account)->not->toBeNull()
        ->and($account->owner_id)->toBe($rep->id)
        ->and($account->shipping_street)->toBe('1 Main St')
        ->and($account->shipping_city)->toBe('Seattle');
});

test('account list requires name validation', function () {
    $rep = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();

    $this->actingAs($rep)
        ->post(route('accounts.store'), ['name' => ''])
        ->assertSessionHasErrors('name');
});

test('read only user cannot create accounts', function () {
    $user = User::factory()->withRole(RoleSlug::ReadOnlyUser->value)->create();

    $this->actingAs($user)
        ->post(route('accounts.store'), ['name' => 'Blocked'])
        ->assertForbidden();
});

test('private sharing hides other owners accounts from sales rep', function () {
    $rep = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    $other = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();

    Account::factory()->ownedBy($other)->create(['name' => 'Hidden Co']);
    Account::factory()->ownedBy($rep)->create(['name' => 'Visible Co']);

    $this->actingAs($rep)
        ->get(route('accounts.index'))
        ->assertOk()
        ->assertSee('Visible Co', false)
        ->assertDontSee('Hidden Co', false);
});

test('change owner writes ownership history', function () {
    $manager = User::factory()->withRole(RoleSlug::SalesManager->value)->create();
    $rep = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    $account = Account::factory()->ownedBy($manager)->create();

    $this->actingAs($manager)
        ->post(route('accounts.change-owner', $account), [
            'owner_id' => $rep->id,
            'notify_new_owner' => '1',
        ])
        ->assertRedirect(route('accounts.show', $account));

    expect($account->fresh()->owner_id)->toBe($rep->id);
    $this->assertDatabaseHas('ownership_histories', [
        'ownable_type' => 'account',
        'ownable_id' => $account->id,
        'previous_owner_id' => $manager->id,
        'new_owner_id' => $rep->id,
        'changed_by' => $manager->id,
    ]);
    expect(OwnershipHistory::query()->count())->toBe(1);
});

test('bulk delete removes selected writable accounts', function () {
    $rep = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    $a = Account::factory()->ownedBy($rep)->create(['name' => 'Delete Me']);
    $b = Account::factory()->ownedBy($rep)->create(['name' => 'Keep Me']);

    $this->actingAs($rep)
        ->post(route('accounts.bulk'), [
            'action' => 'delete',
            'ids' => [$a->id],
        ])
        ->assertRedirect(route('accounts.index'));

    $this->assertDatabaseMissing('accounts', ['id' => $a->id]);
    $this->assertDatabaseHas('accounts', ['id' => $b->id]);
});
