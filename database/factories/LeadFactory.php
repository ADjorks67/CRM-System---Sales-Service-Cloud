<?php

namespace Database\Factories;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lead>
 */
class LeadFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'salutation' => fake()->optional()->randomElement(['mr', 'ms', 'mrs', 'dr', 'prof']),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'company' => fake()->company(),
            'title' => fake()->optional()->jobTitle(),
            'email' => fake()->optional()->safeEmail(),
            'phone' => fake()->optional()->numerify('###-###-####'),
            'mobile' => fake()->optional()->numerify('###-###-####'),
            'status' => 'new',
            'lead_source' => fake()->optional()->randomElement(['web', 'advertisement', 'trade_show', 'social']),
            'rating' => fake()->optional()->randomElement(['hot', 'warm', 'cold']),
            'industry' => fake()->optional()->randomElement(['technology', 'finance', 'healthcare']),
            'annual_revenue' => fake()->optional()->randomFloat(2, 10000, 2000000),
            'number_of_employees' => fake()->optional()->numberBetween(1, 1000),
            'website' => fake()->optional()->url(),
            'street' => fake()->optional()->streetAddress(),
            'city' => fake()->optional()->city(),
            'state' => fake()->optional()->stateAbbr(),
            'postal_code' => fake()->optional()->postcode(),
            'country' => fake()->optional()->country(),
            'description' => fake()->optional()->sentence(),
            'is_converted' => false,
            'converted_account_id' => null,
            'converted_contact_id' => null,
            'converted_opportunity_id' => null,
            'owner_id' => User::factory(),
        ];
    }

    public function ownedBy(User $user): static
    {
        return $this->state(fn () => ['owner_id' => $user->id]);
    }

    public function withStatus(string $status): static
    {
        return $this->state(fn () => ['status' => $status]);
    }

    public function converted(): static
    {
        return $this->state(fn () => [
            'status' => 'converted',
            'is_converted' => true,
            'converted_at' => now(),
        ]);
    }
}
