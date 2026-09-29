<?php

/**
 * SRS §8.2 Attachments.
 */

use App\Enums\RoleSlug;
use App\Models\Account;
use App\Models\Attachment;
use App\Models\User;
use Database\Seeders\PicklistSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(PicklistSeeder::class);
    Storage::fake('attachments');
});

test('user can upload download and delete an attachment on an account', function () {
    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    $account = Account::factory()->ownedBy($user)->create();
    $file = UploadedFile::fake()->create('notes.txt', 10, 'text/plain');

    $this->actingAs($user)
        ->post(route('attachments.store'), [
            'attachable_type' => 'account',
            'attachable_id' => $account->id,
            'file' => $file,
        ])
        ->assertRedirect();

    $attachment = Attachment::query()->first();
    expect($attachment)->not->toBeNull();
    expect($attachment->scan_status)->toBe(Attachment::SCAN_CLEAN);
    Storage::disk('attachments')->assertExists($attachment->path);

    $this->actingAs($user)
        ->get(route('attachments.download', $attachment))
        ->assertOk();

    $this->actingAs($user)
        ->delete(route('attachments.destroy', $attachment))
        ->assertRedirect();

    expect(Attachment::query()->count())->toBe(0);
});

test('eicar content is rejected by scanner', function () {
    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    $account = Account::factory()->ownedBy($user)->create();

    // Use the EICAR marker string (not the full signature) so Windows AV does not quarantine the temp upload.
    $file = UploadedFile::fake()->createWithContent(
        'eicar.txt',
        'malware test: EICAR-STANDARD-ANTIVIRUS-TEST-FILE',
    );

    $this->actingAs($user)
        ->from(route('accounts.show', $account))
        ->post(route('attachments.store'), [
            'attachable_type' => 'account',
            'attachable_id' => $account->id,
            'file' => $file,
        ])
        ->assertRedirect()
        ->assertSessionHasErrors('file');

    expect(Attachment::query()->count())->toBe(0);
});

test('disallowed mime is rejected', function () {
    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    $account = Account::factory()->ownedBy($user)->create();
    $file = UploadedFile::fake()->create('malware.exe', 10, 'application/x-msdownload');

    $this->actingAs($user)
        ->from(route('accounts.show', $account))
        ->post(route('attachments.store'), [
            'attachable_type' => 'account',
            'attachable_id' => $account->id,
            'file' => $file,
        ])
        ->assertRedirect()
        ->assertSessionHasErrors('file');
});

test('other users cannot download attachments on private records', function () {
    $owner = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    $other = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    $account = Account::factory()->ownedBy($owner)->create();
    $attachment = Attachment::factory()->create([
        'attachable_type' => 'account',
        'attachable_id' => $account->id,
        'uploaded_by' => $owner->id,
        'path' => 'account/1/secret.txt',
    ]);
    Storage::disk('attachments')->put($attachment->path, 'secret');

    $this->actingAs($other)
        ->get(route('attachments.download', $attachment))
        ->assertForbidden();
});
