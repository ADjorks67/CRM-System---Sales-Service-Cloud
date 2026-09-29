<?php

use App\Enums\RoleSlug;
use App\Models\Account;
use App\Models\Contact;
use App\Models\CrmCase;
use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('read only user cannot create leads accounts or opportunities', function () {
    $user = User::factory()->withRole(RoleSlug::ReadOnlyUser->value)->create();

    $this->actingAs($user)
        ->get(route('leads.create'))
        ->assertForbidden();

    $this->actingAs($user)
        ->get(route('accounts.create'))
        ->assertForbidden();

    $this->actingAs($user)
        ->get(route('opportunities.create'))
        ->assertForbidden();
});

test('sales representative cannot open another owners private lead', function () {
    $owner = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    $peer = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    $lead = Lead::factory()->ownedBy($owner)->create(['first_name' => 'Secret', 'last_name' => 'Lead']);

    $this->actingAs($peer)
        ->get(route('leads.show', $lead))
        ->assertForbidden();
});

test('service representative cannot create opportunities but can open cases create', function () {
    $user = User::factory()->withRole(RoleSlug::ServiceRepresentative->value)->create();

    $this->actingAs($user)
        ->get(route('opportunities.create'))
        ->assertForbidden();

    $this->actingAs($user)
        ->get(route('cases.create'))
        ->assertOk();
});

test('sales manager can create opportunities', function () {
    $user = User::factory()->withRole(RoleSlug::SalesManager->value)->create();

    $this->actingAs($user)
        ->get(route('opportunities.create'))
        ->assertOk();
});

test('non admin cannot access users or gdpr tools', function () {
    $user = User::factory()->withRole(RoleSlug::SalesManager->value)->create();

    $this->actingAs($user)
        ->get(route('users.index'))
        ->assertForbidden();

    $this->actingAs($user)
        ->get(route('gdpr.index'))
        ->assertForbidden();
});

test('peer cannot view another owners contact under private sharing', function () {
    $owner = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    $peer = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    $account = Account::factory()->create(['owner_id' => $owner->id]);
    $contact = Contact::factory()->forAccount($account)->ownedBy($owner)->create();

    $this->actingAs($peer)
        ->get(route('contacts.show', $contact))
        ->assertForbidden();
});

test('peer cannot view another owners case', function () {
    $owner = User::factory()->withRole(RoleSlug::ServiceRepresentative->value)->create();
    $peer = User::factory()->withRole(RoleSlug::ServiceRepresentative->value)->create();
    $case = CrmCase::factory()->ownedBy($owner)->create(['subject' => 'Private Case']);

    $this->actingAs($peer)
        ->get(route('cases.show', $case))
        ->assertForbidden();
});

test('peer cannot view another owners opportunity', function () {
    $owner = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    $peer = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    $account = Account::factory()->create(['owner_id' => $owner->id]);
    $opportunity = Opportunity::factory()->forAccount($account)->ownedBy($owner)->create([
        'name' => 'Owner Opp',
    ]);

    $this->actingAs($peer)
        ->get(route('opportunities.show', $opportunity))
        ->assertForbidden();
});
