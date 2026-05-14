<?php

namespace Database\Factories;

use App\Models\AssistanceLog;
use App\Models\Beneficiary;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AssistanceLog>
 */
class AssistanceLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'beneficiary_id' => Beneficiary::factory(),
            'assistance_type' => fake()->randomElement(['Medical Assistance', 'Food Relief', 'Educational Support', 'Financial Aid']),
            'description' => fake()->sentence(),
            'amount' => fake()->randomFloat(2, 500, 5000),
            'assisted_at' => fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
        ];
    }
}
