<?php

namespace Database\Factories;

use App\Models\SavedReport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SavedReport>
 */
class SavedReportFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'description' => fake()->optional()->sentence(),
            'folder' => 'private',
            'report_type' => 'lead',
            'definition' => [
                'columns' => ['company', 'status', 'created_at'],
                'filters' => [],
                'group_by' => null,
                'chart' => null,
            ],
            'is_private' => true,
            'owner_id' => User::factory(),
        ];
    }

    public function ownedBy(User $user): static
    {
        return $this->state(fn () => ['owner_id' => $user->id]);
    }
}
