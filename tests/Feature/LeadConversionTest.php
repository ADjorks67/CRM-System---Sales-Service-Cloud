<?php

use App\Enums\LeadStatus;
use App\Enums\RoleSlug;
use App\Models\Account;
use App\Models\Contact;
use App\Models\Event;
use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\User;
use Database\Seeders\PicklistSeeder;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(PicklistSeeder::class);
});

test('sales rep can convert lead into account contact and opportunity', function () {
    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    $lead = Lead::factory()->ownedBy($user)->create([
        'company' => 'Convert Co',
        'first_name' => 'Sam',
        'last_name' => 'Lead',
        'status' => LeadStatus::Qualified,
        'street' => '1 Main',
        'city' => 'Austin',
    ]);

    Event::factory()->ownedBy($user)->create([
        'subject' => 'Lead call',
        'related_type' => 'lead',
        'related_id' => $lead->id,
        'starts_at' => now()->addDay(),
        'ends_at' => now()->addDay()->addHour(),
    ]);

    $this->actingAs($user)
        ->post(route('leads.convert.store', $lead), [
            'account_action' => 'create',
            'account_name' => 'Convert Co',
            'create_opportunity' => '1',
            'opportunity_name' => 'Convert Co Deal',
            'opportunity_amount' => '15000',
            'opportunity_close_date' => now()->addMonth()->toDateString(),
            'opportunity_stage' => 'qualification',
        ])
        ->assertRedirect(route('leads.show', $lead));

    $lead->refresh();
    expect($lead->is_converted)->toBeTrue()
        ->and($lead->status)->toBe(LeadStatus::Converted)
        ->and($lead->converted_at)->not->toBeNull()
        ->and($lead->converted_account_id)->not->toBeNull()
        ->and($lead->converted_contact_id)->not->toBeNull()
        ->and($lead->converted_opportunity_id)->not->toBeNull();

    expect(Account::query()->whereKey($lead->converted_account_id)->value('name'))->toBe('Convert Co');
    expect(Contact::query()->whereKey($lead->converted_contact_id)->value('last_name'))->toBe('Lead');
    expect(Opportunity::query()->whereKey($lead->converted_opportunity_id)->value('name'))->toBe('Convert Co Deal');

    $event = Event::query()->where('subject', 'Lead call')->first();
    expect($event->related_type)->toBe('opportunity')
        ->and($event->related_id)->toBe($lead->converted_opportunity_id)
        ->and($event->name_contact_id)->toBe($lead->converted_contact_id);

    $this->actingAs($user)
        ->get(route('leads.edit', $lead))
        ->assertForbidden();
});

test('convert can match existing account by company', function () {
    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    $account = Account::factory()->ownedBy($user)->create(['name' => 'Match Corp']);
    $lead = Lead::factory()->ownedBy($user)->create([
        'company' => 'Match Corp',
        'status' => LeadStatus::Working,
    ]);

    $this->actingAs($user)
        ->post(route('leads.convert.store', $lead), [
            'account_action' => 'match',
            'account_id' => $account->id,
            'create_opportunity' => '0',
        ])
        ->assertRedirect(route('leads.show', $lead));

    $lead->refresh();
    expect($lead->converted_account_id)->toBe($account->id)
        ->and($lead->converted_opportunity_id)->toBeNull();
});

test('already converted lead cannot convert again', function () {
    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    $lead = Lead::factory()->ownedBy($user)->create([
        'is_converted' => true,
        'status' => LeadStatus::Converted,
        'converted_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('leads.convert', $lead))
        ->assertForbidden();
});
