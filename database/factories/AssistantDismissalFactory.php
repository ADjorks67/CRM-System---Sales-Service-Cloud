<?php

namespace Database\Factories;

use App\Models\AssistantDismissal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AssistantDismissal>
 */
class AssistantDismissalFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'recommendation_key' => 'inactive_account:'.fake()->numberBetween(1, 9999),
            'dismissed_at' => now(),
        ];
    }

    public function forUser(User $user): static
    {
        return $this->state(fn () => ['user_id' => $user->id]);
    }
}
