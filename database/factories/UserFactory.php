<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => Str::uuid(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
        ];
    }

    public function asAdmin()
    {
        return $this->state([
            'is_admin' => 1,
            'password' => bcrypt("admin"),
        ]);
    }

    public function asUser()
    {
        return $this->state([
            'is_admin' => 0,
            'password' => bcrypt("userpassword"),
        ]);
    }
}
