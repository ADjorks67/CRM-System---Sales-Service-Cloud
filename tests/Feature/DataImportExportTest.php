<?php

use App\Enums\RoleSlug;
use App\Models\Account;
use App\Models\Lead;
use App\Models\User;
use Database\Seeders\PicklistSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(PicklistSeeder::class);
    Storage::fake('local');
});

test('user can preview and import leads from csv', function () {
    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();

    $csv = "Last Name,Company,Email,Status,Lead Source\n".
        "Doe,Acme Inc,jane.doe@import.test,new,web\n";

    $file = UploadedFile::fake()->createWithContent('leads.csv', $csv);

    $preview = $this->actingAs($user)
        ->post(route('imports.preview'), [
            'object' => 'leads',
            'file' => $file,
            'update_or_insert' => '1',
        ]);

    $preview->assertOk()->assertSee('Map fields');

    $this->actingAs($user)
        ->post(route('imports.store'), [
            'object' => 'leads',
            'update_or_insert' => '1',
            'mapping' => [
                'last_name' => 0,
                'company' => 1,
                'email' => 2,
                'status' => 3,
                'lead_source' => 4,
            ],
        ])
        ->assertRedirect(route('imports.index'));

    $lead = Lead::query()->where('email', 'jane.doe@import.test')->first();
    expect($lead)->not->toBeNull()
        ->and($lead->last_name)->toBe('Doe')
        ->and($lead->company)->toBe('Acme Inc')
        ->and($lead->owner_id)->toBe($user->id);
});

test('import collects row errors and allows error report download', function () {
    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();

    $csv = "Last Name,Company,Email\n".
        ",Missing Last,bad@import.test\n";

    $file = UploadedFile::fake()->createWithContent('bad-leads.csv', $csv);

    $this->actingAs($user)
        ->post(route('imports.preview'), [
            'object' => 'leads',
            'file' => $file,
            'update_or_insert' => '1',
        ])
        ->assertOk();

    $this->actingAs($user)
        ->post(route('imports.store'), [
            'object' => 'leads',
            'update_or_insert' => '1',
            'mapping' => [
                'last_name' => 0,
                'company' => 1,
                'email' => 2,
            ],
        ])
        ->assertRedirect(route('imports.index'))
        ->assertSessionHas('import_error_count', 1);

    $this->actingAs($user)
        ->get(route('imports.errors'))
        ->assertOk()
        ->assertHeader('content-disposition');
});

test('user can export accounts as csv of visible records', function () {
    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    Account::factory()->ownedBy($user)->create(['name' => 'Exportable Account']);

    $response = $this->actingAs($user)
        ->get(route('exports.download', 'accounts'));

    $response->assertOk();
    expect($response->headers->get('content-disposition'))->toContain('.csv');
    expect($response->streamedContent())->toContain('Exportable Account');
});
