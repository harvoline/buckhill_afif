<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => fake()->unique(true)->uuid(),
            'user_id' => fake()->numberBetween(2, 11),
            'order_status_id' => fake()->numberBetween(1, 5),
            'payment_id' => fake()->randomNumber(5, false),
            'products' => "{}",
            'address' => "{}",
            'amount' => fake()->randomFloat(2),
        ];
    }

    public function withUser($id)
    {
        return $this->state([
            'user_id' => $id,
        ]);
    }
}
