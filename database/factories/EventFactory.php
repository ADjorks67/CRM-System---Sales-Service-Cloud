<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\User;
use Carbon\Carbon;
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
            'is_all_day' => false,
            'location' => fake()->optional()->city(),
            'description' => fake()->optional()->sentence(),
            'show_as' => 'busy',
            'is_private' => false,
            'calendar_type' => 'my_events',
            'color' => null,
            'owner_id' => User::factory(),
            'related_type' => null,
            'related_id' => null,
            'name_contact_id' => null,
        ];
    }

    public function ownedBy(User $user): static
    {
        return $this->state(fn () => ['owner_id' => $user->id]);
    }

    public function private(): static
    {
        return $this->state(fn () => ['is_private' => true]);
    }

    public function allDay(): static
    {
        return $this->state(function (array $attributes): array {
            $day = Carbon::parse($attributes['starts_at'] ?? now())->startOfDay();

            return [
                'is_all_day' => true,
                'starts_at' => $day,
                'ends_at' => $day->copy()->endOfDay(),
            ];
        });
    }

    public function occurringToday(): static
    {
        return $this->state(function (): array {
            $start = now()->setTime(10, 0);

            return [
                'starts_at' => $start,
                'ends_at' => $start->copy()->addHour(),
                'is_all_day' => false,
            ];
        });
    }
}
