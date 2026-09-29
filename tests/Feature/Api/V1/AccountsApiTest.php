<?php

/**
 * SRS §8.3 Accounts API.
 */

use App\Enums\RoleSlug;
use App\Models\Account;
use App\Models\ApiToken;
use App\Models\User;
use Database\Seeders\PicklistSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(PicklistSeeder::class);
});

function accountApiHeaders(User $user): array
{
    $plain = Str::random(40);
    ApiToken::query()->create([
        'user_id' => $user->id,
        'name' => 'Test',
        'token' => hash('sha256', $plain),
    ]);

    return ['Authorization' => 'Bearer '.$plain, 'Accept' => 'application/json'];
}

test('accounts api lists visible records and creates', function () {
    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    Account::factory()->ownedBy($user)->create(['name' => 'API Visible Co']);

    $this->withHeaders(accountApiHeaders($user))
        ->getJson('/api/v1/accounts')
        ->assertOk()
        ->assertJsonFragment(['name' => 'API Visible Co']);

    $this->withHeaders(accountApiHeaders($user))
        ->postJson('/api/v1/accounts', ['name' => 'Created Via API'])
        ->assertCreated()
        ->assertJsonFragment(['name' => 'Created Via API']);
});

test('accounts api hides private records from other users', function () {
    $owner = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    $other = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    Account::factory()->ownedBy($owner)->create(['name' => 'Secret API Account']);

    $this->withHeaders(accountApiHeaders($other))
        ->getJson('/api/v1/accounts')
        ->assertOk()
        ->assertJsonMissing(['name' => 'Secret API Account']);
});

test('accounts api metadata endpoint works', function () {
    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();

    $this->withHeaders(accountApiHeaders($user))
        ->getJson('/api/v1/metadata/accounts')
        ->assertOk()
        ->assertJsonPath('object', 'accounts');
});
