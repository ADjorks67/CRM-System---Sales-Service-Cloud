<?php

use App\Enums\RoleSlug;
use App\Models\Account;
use App\Models\Contact;
use App\Models\User;
use Database\Seeders\PicklistSeeder;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(PicklistSeeder::class);
});

test('sales rep can create contact with required account and last name', function () {
    $rep = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    $account = Account::factory()->ownedBy($rep)->create();

    $this->actingAs($rep)
        ->post(route('contacts.store'), [
            'last_name' => 'Nguyen',
            'first_name' => 'Taylor',
            'account_id' => $account->id,
            'email' => 'taylor@example.com',
            'save_action' => 'save',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('contacts', [
        'last_name' => 'Nguyen',
        'account_id' => $account->id,
        'owner_id' => $rep->id,
    ]);
});

test('contact create requires account and last name', function () {
    $rep = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();

    $this->actingAs($rep)
        ->post(route('contacts.store'), [])
        ->assertSessionHasErrors(['last_name', 'account_id']);
});

test('contact detail links to account', function () {
    $rep = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    $account = Account::factory()->ownedBy($rep)->create(['name' => 'Linked Account']);
    $contact = Contact::factory()->forAccount($account)->create([
        'first_name' => 'Pat',
        'last_name' => 'Lee',
    ]);

    $this->actingAs($rep)
        ->get(route('contacts.show', $contact))
        ->assertOk()
        ->assertSee('Pat Lee', false)
        ->assertSee('Linked Account', false);
});

test('read only user cannot update contacts', function () {
    $readonly = User::factory()->withRole(RoleSlug::ReadOnlyUser->value)->create();
    $contact = Contact::factory()->ownedBy($readonly)->create();

    $this->actingAs($readonly)
        ->put(route('contacts.update', $contact), [
            'last_name' => 'Nope',
            'account_id' => $contact->account_id,
        ])
        ->assertForbidden();
});
