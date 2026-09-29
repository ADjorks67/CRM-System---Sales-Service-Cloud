<?php

use App\Enums\RoleSlug;
use App\Models\CrmCase;
use App\Models\User;
use Database\Seeders\PicklistSeeder;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(PicklistSeeder::class);
});

test('service rep can create case with auto case number', function () {
    $user = User::factory()->withRole(RoleSlug::ServiceRepresentative->value)->create();

    $this->actingAs($user)
        ->post(route('cases.store'), [
            'subject' => 'Login issue',
            'status' => 'new',
            'origin' => 'web',
            'priority' => 'high',
            'save_action' => 'save',
        ])
        ->assertRedirect();

    $case = CrmCase::query()->where('subject', 'Login issue')->first();
    expect($case)->not->toBeNull()
        ->and($case->owner_id)->toBe($user->id)
        ->and($case->case_number)->toMatch('/^CASE-\d{4}-\d{5}$/');
});

test('case index filters my open all open and recently closed', function () {
    $owner = User::factory()->withRole(RoleSlug::ServiceRepresentative->value)->create();
    $other = User::factory()->withRole(RoleSlug::ServiceRepresentative->value)->create();
    $admin = User::factory()->withRole(RoleSlug::SystemAdministrator->value)->create();

    CrmCase::factory()->ownedBy($owner)->withStatus('working')->create(['subject' => 'Mine open']);
    CrmCase::factory()->ownedBy($other)->withStatus('new')->create(['subject' => 'Theirs open']);
    CrmCase::factory()->ownedBy($owner)->closed()->create(['subject' => 'Mine closed']);

    $this->actingAs($owner)
        ->get(route('cases.index', ['view' => 'my_open']))
        ->assertOk()
        ->assertSee('Mine open', false)
        ->assertDontSee('Theirs open', false)
        ->assertDontSee('Mine closed', false);

    // Private OWD: only System Administrator sees other owners' cases in All Open.
    $this->actingAs($admin)
        ->get(route('cases.index', ['view' => 'all_open']))
        ->assertOk()
        ->assertSee('Mine open', false)
        ->assertSee('Theirs open', false)
        ->assertDontSee('Mine closed', false);

    $this->actingAs($owner)
        ->get(route('cases.index', ['view' => 'recently_closed']))
        ->assertOk()
        ->assertSee('Mine closed', false)
        ->assertDontSee('Mine open', false);
});

test('closed case is read only for updates', function () {
    $user = User::factory()->withRole(RoleSlug::ServiceRepresentative->value)->create();
    $case = CrmCase::factory()->ownedBy($user)->closed()->create();

    $this->actingAs($user)
        ->put(route('cases.update', $case), [
            'subject' => 'Changed',
            'status' => 'closed',
            'origin' => 'web',
        ])
        ->assertForbidden();
});

test('closed case can be reopened', function () {
    $user = User::factory()->withRole(RoleSlug::ServiceRepresentative->value)->create();
    $case = CrmCase::factory()->ownedBy($user)->closed()->create();

    $this->actingAs($user)
        ->post(route('cases.reopen', $case))
        ->assertRedirect(route('cases.show', $case));

    $case->refresh();
    expect($case->status)->toBe('working')
        ->and($case->closed_at)->toBeNull();
});

test('change owner records ownership history', function () {
    $owner = User::factory()->withRole(RoleSlug::ServiceRepresentative->value)->create();
    $newOwner = User::factory()->withRole(RoleSlug::ServiceRepresentative->value)->create();
    $case = CrmCase::factory()->ownedBy($owner)->create();

    $this->actingAs($owner)
        ->post(route('cases.change-owner', $case), [
            'owner_id' => $newOwner->id,
            'notify_new_owner' => '1',
            'transfer_open_activities' => '1',
        ])
        ->assertRedirect(route('cases.show', $case));

    $this->assertDatabaseHas('ownership_histories', [
        'ownable_type' => 'case',
        'ownable_id' => $case->id,
        'previous_owner_id' => $owner->id,
        'new_owner_id' => $newOwner->id,
    ]);

    expect($case->fresh()->owner_id)->toBe($newOwner->id);
});

test('change status sets closed timestamp when closed', function () {
    $user = User::factory()->withRole(RoleSlug::ServiceRepresentative->value)->create();
    $case = CrmCase::factory()->ownedBy($user)->withStatus('working')->create();

    $this->actingAs($user)
        ->post(route('cases.change-status', $case), [
            'status' => 'closed',
        ])
        ->assertRedirect(route('cases.show', $case));

    $case->refresh();
    expect($case->status)->toBe('closed')
        ->and($case->closed_at)->not->toBeNull();
});

test('bulk change status updates selected cases', function () {
    $user = User::factory()->withRole(RoleSlug::ServiceRepresentative->value)->create();
    $cases = CrmCase::factory()->count(2)->ownedBy($user)->create(['status' => 'new']);

    $this->actingAs($user)
        ->post(route('cases.bulk'), [
            'action' => 'change_status',
            'ids' => $cases->pluck('id')->all(),
            'status' => 'escalated',
        ])
        ->assertRedirect(route('cases.index'));

    expect(
        CrmCase::query()
            ->whereIn('id', $cases->pluck('id'))
            ->where('status', 'escalated')
            ->count()
    )->toBe(2);
});

test('sales rep cannot create cases', function () {
    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();

    $this->actingAs($user)
        ->post(route('cases.store'), [
            'subject' => 'Nope',
            'status' => 'new',
            'origin' => 'web',
        ])
        ->assertForbidden();
});
