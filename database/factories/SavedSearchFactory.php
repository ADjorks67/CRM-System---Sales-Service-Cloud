<?php

namespace Database\Factories;

use App\Models\SavedSearch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SavedSearch>
 */
class SavedSearchFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'owner_id' => User::factory(),
            'object_type' => 'account',
            'definition' => [
                'object_type' => 'account',
                'conditions' => [
                    [
                        'field' => 'name',
                        'operator' => 'contains',
                        'value' => 'Acme',
                        'logic' => 'AND',
                    ],
                ],
                'date_from' => null,
                'date_to' => null,
                'date_field' => 'created_at',
            ],
        ];
    }

    public function ownedBy(User $user): static
    {
        return $this->state(fn () => ['owner_id' => $user->id]);
    }
}
