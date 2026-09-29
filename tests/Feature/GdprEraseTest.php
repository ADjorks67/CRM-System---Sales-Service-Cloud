<?php

use App\Enums\RoleSlug;
use App\Models\Account;
use App\Models\Contact;
use App\Models\GdprAudit;
use App\Models\Lead;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('admin can anonymize contact pii and retain the row', function () {
    $admin = User::factory()->withRole(RoleSlug::SystemAdministrator->value)->create();
    $account = Account::factory()->create(['owner_id' => $admin->id]);
    $contact = Contact::factory()->forAccount($account)->create([
        'email' => 'erase.me@example.test',
        'first_name' => 'Erase',
        'last_name' => 'Me',
        'phone' => '555-0100',
    ]);

    $this->actingAs($admin)
        ->post(route('gdpr.anonymize'), [
            'subject_type' => 'contact',
            'subject_id' => $contact->id,
        ])
        ->assertRedirect(route('gdpr.index'));

    $contact->refresh();

    expect($contact->first_name)->toBe('Anonymized')
        ->and($contact->email)->toEndWith('@anonymized.invalid')
        ->and($contact->phone)->toBeNull()
        ->and(GdprAudit::query()->where('action', 'anonymize')->where('subject_id', $contact->id)->exists())->toBeTrue();
});

test('admin can anonymize lead pii', function () {
    $admin = User::factory()->withRole(RoleSlug::SystemAdministrator->value)->create();
    $lead = Lead::factory()->ownedBy($admin)->create([
        'email' => 'lead.erase@example.test',
        'first_name' => 'Lead',
        'last_name' => 'Erase',
    ]);

    $this->actingAs($admin)
        ->post(route('gdpr.anonymize'), [
            'subject_type' => 'lead',
            'subject_id' => $lead->id,
        ])
        ->assertRedirect(route('gdpr.index'));

    $lead->refresh();

    expect($lead->email)->toEndWith('@anonymized.invalid')
        ->and($lead->first_name)->toBe('Anonymized');
});

test('admin cannot anonymize own user account', function () {
    $admin = User::factory()->withRole(RoleSlug::SystemAdministrator->value)->create();

    $this->actingAs($admin)
        ->from(route('gdpr.index'))
        ->post(route('gdpr.anonymize'), [
            'subject_type' => 'user',
            'subject_id' => $admin->id,
        ])
        ->assertRedirect(route('gdpr.index'))
        ->assertSessionHasErrors('subject_id');

    $admin->refresh();

    expect($admin->email)->not->toEndWith('@anonymized.invalid');
});

test('non admin cannot anonymize', function () {
    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    $lead = Lead::factory()->create();

    $this->actingAs($user)
        ->post(route('gdpr.anonymize'), [
            'subject_type' => 'lead',
            'subject_id' => $lead->id,
        ])
        ->assertForbidden();
});
