<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Opportunity;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Opportunity>
 */
class OpportunityFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'account_id' => Account::factory(),
            'amount' => fake()->optional()->randomFloat(2, 1000, 500000),
            'close_date' => fake()->dateTimeBetween('now', '+6 months')->format('Y-m-d'),
            'stage' => 'qualification',
            'probability' => 10,
            'type' => null,
            'lead_source' => fake()->optional()->randomElement(['web', 'advertisement', 'trade_show', 'social']),
            'next_step' => fake()->optional()->sentence(),
            'description' => fake()->optional()->sentence(),
            'expected_revenue' => null,
            'is_closed' => false,
            'is_won' => false,
            'archived_at' => null,
            'owner_id' => User::factory(),
        ];
    }

    public function ownedBy(User $user): static
    {
        return $this->state(fn () => ['owner_id' => $user->id]);
    }

    public function forAccount(Account $account): static
    {
        return $this->state(fn () => ['account_id' => $account->id]);
    }

    public function withStage(string $stage, ?int $probability = null): static
    {
        return $this->state(function () use ($stage, $probability): array {
            $probability ??= match ($stage) {
                'qualification' => 10,
                'meeting_scheduled' => 20,
                'proposal_price_quote' => 65,
                'negotiation_review' => 80,
                'closed_won' => 100,
                'closed_lost' => 0,
                default => 10,
            };

            return [
                'stage' => $stage,
                'probability' => $probability,
                'is_closed' => in_array($stage, ['closed_won', 'closed_lost'], true),
                'is_won' => $stage === 'closed_won',
            ];
        });
    }
}
