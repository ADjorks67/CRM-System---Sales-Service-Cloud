<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= 'Password1!',
            'remember_token' => Str::random(10),
            'is_active' => true,
            'failed_login_attempts' => 0,
            'locked_until' => null,
            'role_id' => null,
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function withRole(string $slug): static
    {
        return $this->state(function () use ($slug) {
            $role = Role::query()->where('slug', $slug)->first();

            return [
                'role_id' => $role?->id,
            ];
        });
    }

    public function locked(): static
    {
        return $this->state(fn (array $attributes) => [
            'locked_until' => now()->addMinutes(30),
            'failed_login_attempts' => 0,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
