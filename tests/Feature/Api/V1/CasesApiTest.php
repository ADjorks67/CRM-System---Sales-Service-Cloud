<?php

/**
 * SRS §8.3 Cases API.
 */

use App\Enums\RoleSlug;
use App\Models\ApiToken;
use App\Models\CrmCase;
use App\Models\User;
use Database\Seeders\PicklistSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(PicklistSeeder::class);
});

function caseApiHeaders(User $user): array
{
    $plain = Str::random(40);
    ApiToken::query()->create([
        'user_id' => $user->id,
        'name' => 'Test',
        'token' => hash('sha256', $plain),
    ]);

    return ['Authorization' => 'Bearer '.$plain, 'Accept' => 'application/json'];
}

test('cases api lists owned cases', function () {
    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    CrmCase::factory()->ownedBy($user)->create(['subject' => 'API Case Subject']);

    $this->withHeaders(caseApiHeaders($user))
        ->getJson('/api/v1/cases')
        ->assertOk()
        ->assertJsonFragment(['subject' => 'API Case Subject']);
});
