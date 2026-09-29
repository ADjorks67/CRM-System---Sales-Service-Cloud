<?php

use App\Enums\RoleSlug;
use App\Models\Account;
use App\Models\Contact;
use App\Models\GdprAudit;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('admin can export contact personal data as json', function () {
    $admin = User::factory()->withRole(RoleSlug::SystemAdministrator->value)->create();
    $account = Account::factory()->create(['owner_id' => $admin->id]);
    $contact = Contact::factory()->forAccount($account)->create([
        'email' => 'export.me@example.test',
        'first_name' => 'Export',
        'last_name' => 'Subject',
    ]);

    $response = $this->actingAs($admin)
        ->post(route('gdpr.export'), [
            'subject_type' => 'contact',
            'subject_id' => $contact->id,
        ]);

    $response->assertOk();
    expect($response->streamedContent())->toContain('export.me@example.test');

    expect(GdprAudit::query()->where('action', 'export')->where('subject_id', $contact->id)->exists())->toBeTrue();
});

test('admin can export user personal data as json', function () {
    $admin = User::factory()->withRole(RoleSlug::SystemAdministrator->value)->create();
    $subject = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create([
        'email' => 'user.export@example.test',
        'name' => 'Export User',
    ]);

    $response = $this->actingAs($admin)
        ->post(route('gdpr.export'), [
            'subject_type' => 'user',
            'subject_id' => $subject->id,
        ]);

    $response->assertOk();
    expect($response->streamedContent())->toContain('user.export@example.test');
});
