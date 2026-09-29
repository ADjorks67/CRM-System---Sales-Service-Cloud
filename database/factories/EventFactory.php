<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = fake()->dateTimeBetween('now', '+14 days');

        return [
            'subject' => fake()->sentence(3),
            'starts_at' => $start,
            'ends_at' => (clone $start)->modify('+1 hour'),
            'all_day' => false,
            'location' => fake()->optional()->city(),
            'description' => fake()->optional()->paragraph(),
            'is_private' => false,
            'related_type' => null,
            'related_id' => null,
            'owner_id' => User::factory(),
        ];
    }

    public function ownedBy(User $user): static
    {
        return $this->state(fn () => ['owner_id' => $user->id]);
    }
}
