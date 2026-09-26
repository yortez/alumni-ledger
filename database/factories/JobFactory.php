<?php

namespace Database\Factories;

use App\Models\Job;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Job>
 */
class JobFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'created_by' => User::factory(),
            'title' => fake()->jobTitle(),
            'company' => fake()->company(),
            'location' => fake()->city(),
            'employment_type' => fake()->randomElement(['Full-time', 'Part-time', 'Contract']),
            'description' => fake()->paragraphs(3, true),
            'requirements' => fake()->paragraphs(2, true),
            'application_deadline' => now()->addMonth()->toDateString(),
            'status' => 'published',
        ];
    }
}
