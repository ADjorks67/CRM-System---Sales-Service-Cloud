<?php

use App\Enums\RoleSlug;
use App\Models\Account;
use App\Models\Contact;
use App\Models\Lead;
use App\Models\User;
use Database\Seeders\PicklistSeeder;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(PicklistSeeder::class);
});

/**
 * Authorization matrix for Phase 2 core objects (FR-AUTH-003 + Gate 2).
 */
dataset('role_permissions', [
    'system administrator' => [RoleSlug::SystemAdministrator, true, true, true],
    'sales manager' => [RoleSlug::SalesManager, true, true, true],
    'sales representative' => [RoleSlug::SalesRepresentative, true, true, true],
    'service representative' => [RoleSlug::ServiceRepresentative, true, true, false],
    'read only user' => [RoleSlug::ReadOnlyUser, false, false, false],
]);

test('role matrix for accounts contacts leads mutate permissions', function (RoleSlug $role, bool $canMutateAccounts, bool $canMutateContacts, bool $canMutateLeads) {
    $user = User::factory()->withRole($role->value)->create();

    expect($user->hasPermission('accounts.create'))->toBe($canMutateAccounts)
        ->and($user->hasPermission('contacts.create'))->toBe($canMutateContacts)
        ->and($user->hasPermission('leads.create'))->toBe($canMutateLeads)
        ->and($user->hasPermission('accounts.view'))->toBeTrue()
        ->and($user->hasPermission('contacts.view'))->toBeTrue()
        ->and($user->hasPermission('leads.view'))->toBeTrue();
})->with('role_permissions');

test('admin sees all private records while rep sees only owned', function () {
    $admin = User::factory()->withRole(RoleSlug::SystemAdministrator->value)->create();
    $rep = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    $other = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();

    $owned = Account::factory()->ownedBy($rep)->create(['name' => 'Rep Account']);
    $foreign = Account::factory()->ownedBy($other)->create(['name' => 'Other Account']);

    expect(Account::query()->visibleTo($rep)->pluck('id')->all())
        ->toContain($owned->id)
        ->not->toContain($foreign->id);

    expect(Account::query()->visibleTo($admin)->pluck('id')->all())
        ->toContain($owned->id)
        ->toContain($foreign->id);
});

test('read only can view owned lead but cannot delete', function () {
    $readonly = User::factory()->withRole(RoleSlug::ReadOnlyUser->value)->create();
    $lead = Lead::factory()->ownedBy($readonly)->create();

    $this->actingAs($readonly)
        ->get(route('leads.show', $lead))
        ->assertOk();

    $this->actingAs($readonly)
        ->delete(route('leads.destroy', $lead))
        ->assertForbidden();
});

test('contact sharing respects private defaults', function () {
    $rep = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    $other = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    $account = Account::factory()->ownedBy($other)->create();
    $contact = Contact::factory()->forAccount($account)->ownedBy($other)->create([
        'last_name' => 'Hidden',
    ]);

    expect(Contact::query()->visibleTo($rep)->whereKey($contact->id)->exists())->toBeFalse();
});
