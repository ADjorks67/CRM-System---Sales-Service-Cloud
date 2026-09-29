<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Account>
 */
class AccountFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'parent_account_id' => null,
            'phone' => fake()->optional()->numerify('###-###-####'),
            'fax' => null,
            'website' => fake()->optional()->url(),
            'type' => fake()->optional()->randomElement(['customer', 'prospect', 'partner', 'other']),
            'industry' => fake()->optional()->randomElement(['technology', 'finance', 'healthcare', 'retail']),
            'employees' => fake()->optional()->numberBetween(1, 5000),
            'annual_revenue' => fake()->optional()->randomFloat(2, 10000, 5000000),
            'billing_street' => fake()->optional()->streetAddress(),
            'billing_city' => fake()->optional()->city(),
            'billing_state' => fake()->optional()->stateAbbr(),
            'billing_postal_code' => fake()->optional()->postcode(),
            'billing_country' => fake()->optional()->country(),
            'shipping_street' => null,
            'shipping_city' => null,
            'shipping_state' => null,
            'shipping_postal_code' => null,
            'shipping_country' => null,
            'description' => fake()->optional()->sentence(),
            'owner_id' => User::factory(),
        ];
    }

    public function ownedBy(User $user): static
    {
        return $this->state(fn () => ['owner_id' => $user->id]);
    }
}
