<?php

/**
 * SRS §8.3 Contacts API.
 */

use App\Enums\RoleSlug;
use App\Models\Account;
use App\Models\ApiToken;
use App\Models\Contact;
use App\Models\User;
use Database\Seeders\PicklistSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(PicklistSeeder::class);
});

function contactApiHeaders(User $user): array
{
    $plain = Str::random(40);
    ApiToken::query()->create([
        'user_id' => $user->id,
        'name' => 'Test',
        'token' => hash('sha256', $plain),
    ]);

    return ['Authorization' => 'Bearer '.$plain, 'Accept' => 'application/json'];
}

test('contacts api lists owned contacts', function () {
    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    $account = Account::factory()->ownedBy($user)->create();
    Contact::factory()->ownedBy($user)->create([
        'account_id' => $account->id,
        'last_name' => 'ApiContact',
    ]);

    $this->withHeaders(contactApiHeaders($user))
        ->getJson('/api/v1/contacts')
        ->assertOk()
        ->assertJsonFragment(['last_name' => 'ApiContact']);
});
