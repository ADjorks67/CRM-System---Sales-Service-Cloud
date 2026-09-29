<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contact>
 */
class ContactFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'salutation' => fake()->optional()->randomElement(['mr', 'ms', 'mrs', 'dr', 'prof']),
            'first_name' => fake()->firstName(),
            'middle_name' => null,
            'last_name' => fake()->lastName(),
            'title' => fake()->optional()->jobTitle(),
            'department' => fake()->optional()->word(),
            'phone' => fake()->optional()->numerify('###-###-####'),
            'mobile' => fake()->optional()->numerify('###-###-####'),
            'home_phone' => null,
            'other_phone' => null,
            'email' => fake()->optional()->safeEmail(),
            'fax' => null,
            'reports_to_id' => null,
            'assistant' => null,
            'asst_phone' => null,
            'mailing_street' => fake()->optional()->streetAddress(),
            'mailing_city' => fake()->optional()->city(),
            'mailing_state' => fake()->optional()->stateAbbr(),
            'mailing_postal_code' => fake()->optional()->postcode(),
            'mailing_country' => fake()->optional()->country(),
            'other_street' => null,
            'other_city' => null,
            'other_state' => null,
            'other_postal_code' => null,
            'other_country' => null,
            'lead_source' => fake()->optional()->randomElement(['web', 'advertisement', 'trade_show']),
            'birthdate' => null,
            'description' => fake()->optional()->sentence(),
            'owner_id' => User::factory(),
        ];
    }

    public function ownedBy(User $user): static
    {
        return $this->state(fn () => ['owner_id' => $user->id]);
    }

    public function forAccount(Account $account): static
    {
        return $this->state(fn () => [
            'account_id' => $account->id,
            'owner_id' => $account->owner_id,
        ]);
    }
}
