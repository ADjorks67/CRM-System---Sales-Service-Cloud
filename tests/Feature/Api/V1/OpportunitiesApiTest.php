<?php

/**
 * SRS §8.3 Opportunities API.
 */

use App\Enums\RoleSlug;
use App\Models\Account;
use App\Models\ApiToken;
use App\Models\Opportunity;
use App\Models\User;
use Database\Seeders\PicklistSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(PicklistSeeder::class);
});

function opportunityApiHeaders(User $user): array
{
    $plain = Str::random(40);
    ApiToken::query()->create([
        'user_id' => $user->id,
        'name' => 'Test',
        'token' => hash('sha256', $plain),
    ]);

    return ['Authorization' => 'Bearer '.$plain, 'Accept' => 'application/json'];
}

test('opportunities api lists open deals', function () {
    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    $account = Account::factory()->ownedBy($user)->create();
    Opportunity::factory()->ownedBy($user)->create([
        'account_id' => $account->id,
        'name' => 'API Deal',
        'close_date' => now()->addMonth()->toDateString(),
    ]);

    $this->withHeaders(opportunityApiHeaders($user))
        ->getJson('/api/v1/opportunities')
        ->assertOk()
        ->assertJsonFragment(['name' => 'API Deal']);
});
