<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'subject' => fake()->sentence(4),
            'status' => 'not_started',
            'priority' => fake()->randomElement(['high', 'normal', 'low']),
            'due_date' => fake()->optional()->dateTimeBetween('now', '+30 days')?->format('Y-m-d'),
            'comments' => fake()->optional()->paragraph(),
            'reminder_set' => false,
            'reminder_at' => null,
            'completed_at' => null,
            'related_type' => null,
            'related_id' => null,
            'contact_id' => null,
            'owner_id' => User::factory(),
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
            'completed_at' => $status === 'completed' ? now() : null,
        ]);
    }

    public function completed(): static
    {
        return $this->withStatus('completed');
    }

    public function dueToday(): static
    {
        return $this->state(fn () => [
            'due_date' => now()->toDateString(),
            'status' => 'not_started',
            'completed_at' => null,
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn () => [
            'due_date' => now()->subDay()->toDateString(),
            'status' => 'not_started',
            'completed_at' => null,
        ]);
    }
}
