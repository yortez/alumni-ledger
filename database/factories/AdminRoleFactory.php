<?php

namespace Database\Factories;

use App\Models\AdminRole;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AdminRole>
 */
class AdminRoleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'type' => fake()->unique()->bothify('custom_????'),
            'description' => fake()->sentence(),
            'permissions' => ['graduates'],
            'is_system' => false,
        ];
    }
}
