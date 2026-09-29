<?php

use App\Enums\LeadStatus;
use App\Enums\RoleSlug;
use App\Models\Lead;
use App\Models\User;
use Database\Seeders\PicklistSeeder;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(PicklistSeeder::class);
});

test('lead requires last name and company', function () {
    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();

    $this->actingAs($user)
        ->post(route('leads.store'), [
            'first_name' => 'Only',
        ])
        ->assertSessionHasErrors(['last_name', 'company']);
});

test('sales rep can create lead with default new status', function () {
    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();

    $this->actingAs($user)
        ->post(route('leads.store'), [
            'last_name' => 'Lead',
            'company' => 'Lead Co',
            'status' => LeadStatus::New->value,
            'lead_source' => 'web',
            'save_action' => 'save',
        ])
        ->assertRedirect();

    $lead = Lead::query()->where('company', 'Lead Co')->first();
    expect($lead)->not->toBeNull()
        ->and($lead->status)->toBe(LeadStatus::New)
        ->and($lead->owner_id)->toBe($user->id);
});

test('lead status can be changed from detail', function () {
    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    $lead = Lead::factory()->ownedBy($user)->create(['status' => LeadStatus::New->value]);

    $this->actingAs($user)
        ->post(route('leads.change-status', $lead), [
            'status' => LeadStatus::Working->value,
        ])
        ->assertRedirect(route('leads.show', $lead));

    expect($lead->fresh()->status)->toBe(LeadStatus::Working);
});

test('converted lead is read only for updates', function () {
    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    $lead = Lead::factory()->converted()->ownedBy($user)->create();

    $this->actingAs($user)
        ->put(route('leads.update', $lead), [
            'last_name' => 'Changed',
            'company' => 'Changed Co',
            'status' => LeadStatus::Converted->value,
        ])
        ->assertForbidden();
});

test('convert button is disabled pending phase 4', function () {
    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    $lead = Lead::factory()->ownedBy($user)->create();

    $this->actingAs($user)
        ->get(route('leads.show', $lead))
        ->assertOk()
        ->assertSee('Lead conversion arrives in Phase 4', false)
        ->assertSee('Convert', false);
});

test('change owner records ownership history', function () {
    $owner = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    $newOwner = User::factory()->withRole(RoleSlug::SalesManager->value)->create();
    $lead = Lead::factory()->ownedBy($owner)->create();

    $this->actingAs($owner)
        ->post(route('leads.change-owner', $lead), [
            'owner_id' => $newOwner->id,
            'notify_new_owner' => '1',
            'transfer_open_activities' => '1',
        ])
        ->assertRedirect(route('leads.show', $lead));

    $this->assertDatabaseHas('ownership_histories', [
        'ownable_type' => 'lead',
        'ownable_id' => $lead->id,
        'previous_owner_id' => $owner->id,
        'new_owner_id' => $newOwner->id,
        'notify_new_owner' => true,
        'transfer_open_activities' => true,
    ]);

    expect($lead->fresh()->owner_id)->toBe($newOwner->id);
});

test('bulk change status updates selected leads', function () {
    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    $leads = Lead::factory()->count(2)->ownedBy($user)->create(['status' => LeadStatus::New->value]);

    $this->actingAs($user)
        ->post(route('leads.bulk'), [
            'action' => 'change_status',
            'ids' => $leads->pluck('id')->all(),
            'status' => LeadStatus::Nurturing->value,
        ])
        ->assertRedirect(route('leads.index'));

    expect(
        Lead::query()
            ->whereIn('id', $leads->pluck('id'))
            ->where('status', LeadStatus::Nurturing->value)
            ->count()
    )->toBe(2);
});

test('service rep cannot create leads', function () {
    $user = User::factory()->withRole(RoleSlug::ServiceRepresentative->value)->create();

    $this->actingAs($user)
        ->post(route('leads.store'), [
            'last_name' => 'Nope',
            'company' => 'Nope Co',
            'status' => LeadStatus::New->value,
        ])
        ->assertForbidden();
});
