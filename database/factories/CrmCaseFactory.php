<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Contact;
use App\Models\CrmCase;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CrmCase>
 */
class CrmCaseFactory extends Factory
{
    protected $model = CrmCase::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'subject' => fake()->optional()->sentence(4),
            'status' => 'new',
            'priority' => fake()->randomElement(['high', 'medium', 'low']),
            'origin' => fake()->randomElement(['phone', 'email', 'web', 'chat']),
            'type' => fake()->optional()->randomElement(['question', 'problem', 'feature_request']),
            'reason' => fake()->optional()->randomElement([
                'complex_functionality',
                'existing_problem',
                'instructions_not_clear',
                'new_problem',
                'user_didnt_attend_training',
                'other',
            ]),
            'description' => fake()->optional()->paragraph(),
            'internal_comments' => fake()->optional()->sentence(),
            'contact_id' => null,
            'account_id' => null,
            'web_email' => fake()->optional()->safeEmail(),
            'web_company' => fake()->optional()->company(),
            'web_name' => fake()->optional()->name(),
            'web_phone' => fake()->optional()->numerify('###-###-####'),
            'owner_id' => User::factory(),
            'closed_at' => null,
        ];
    }

    public function ownedBy(User $user): static
    {
        return $this->state(fn () => ['owner_id' => $user->id]);
    }

    public function withStatus(string $status): static
    {
        return $this->state(fn () => [
            'status' => $status,
            'closed_at' => $status === 'closed' ? now() : null,
        ]);
    }

    public function closed(): static
    {
        return $this->withStatus('closed');
    }

    public function forAccount(Account $account): static
    {
        return $this->state(fn () => ['account_id' => $account->id]);
    }

    public function forContact(Contact $contact): static
    {
        return $this->state(fn () => ['contact_id' => $contact->id]);
    }
}
