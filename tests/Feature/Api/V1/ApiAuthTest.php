<?php

/**
 * SRS §8.3 REST API auth.
 */

use App\Enums\RoleSlug;
use App\Models\ApiToken;
use App\Models\User;
use Database\Seeders\PicklistSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(PicklistSeeder::class);
});

function apiHeaders(User $user, ?string &$plain = null): array
{
    $plain = Str::random(40);
    ApiToken::query()->create([
        'user_id' => $user->id,
        'name' => 'Test',
        'token' => hash('sha256', $plain),
    ]);

    return ['Authorization' => 'Bearer '.$plain, 'Accept' => 'application/json'];
}

test('api rejects missing bearer token', function () {
    $this->getJson('/api/v1/accounts')
        ->assertUnauthorized();
});

test('api rejects invalid bearer token', function () {
    $this->withHeaders(['Authorization' => 'Bearer invalid', 'Accept' => 'application/json'])
        ->getJson('/api/v1/accounts')
        ->assertUnauthorized();
});

test('user can create and revoke api tokens via ui', function () {
    $user = User::factory()->withRole(RoleSlug::SalesRepresentative->value)->create();

    $this->actingAs($user)
        ->post(route('api-tokens.store'), ['name' => 'CI Token'])
        ->assertRedirect(route('api-tokens.index'))
        ->assertSessionHas('api_token_plain');

    $token = ApiToken::query()->where('user_id', $user->id)->first();
    expect($token)->not->toBeNull();

    $this->actingAs($user)
        ->delete(route('api-tokens.destroy', $token))
        ->assertRedirect(route('api-tokens.index'));

    expect(ApiToken::query()->count())->toBe(0);
});
