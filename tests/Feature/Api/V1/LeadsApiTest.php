<?php

/**
 * SRS §8.3 Leads API.
 */

use App\Enums\RoleSlug;
use App\Models\ApiToken;
use App\Models\Lead;
use App\Models\User;
use Database\Seeders\PicklistSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(PicklistSeeder::class);
});

function leadApiHeaders(User $user): array
{
    $plain = Str::random(40);
    ApiToken::query()->create([
        'user_id' => $user->id,
        'name' => 'Test',
        'token' => hash('sha256', $plain),
    ]);

    return ['Authorization' => 'Bearer '.$plain, 'Accept' => 'application/json'];
}

test('leads api show respects policy deny', function () {
    $owner = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    $other = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    $lead = Lead::factory()->ownedBy($owner)->create();

    $this->withHeaders(leadApiHeaders($other))
        ->getJson('/api/v1/leads/'.$lead->id)
        ->assertForbidden();
});

test('leads api happy path index and show', function () {
    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();
    $lead = Lead::factory()->ownedBy($user)->create(['company' => 'Lead API Co']);

    $this->withHeaders(leadApiHeaders($user))
        ->getJson('/api/v1/leads')
        ->assertOk()
        ->assertJsonFragment(['company' => 'Lead API Co']);

    $this->withHeaders(leadApiHeaders($user))
        ->getJson('/api/v1/leads/'.$lead->id)
        ->assertOk()
        ->assertJsonPath('data.company', 'Lead API Co');
});
