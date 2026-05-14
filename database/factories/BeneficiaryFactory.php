<?php

namespace Database\Factories;

use App\Models\Beneficiary;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Beneficiary>
 */
class BeneficiaryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'full_name' => fake()->name(),
            'address' => fake()->streetAddress(),
            'barangay' => fake()->randomElement(['Barangay 1', 'Barangay 2', 'Barangay 3', 'Barangay 4']),
            'birthdate' => fake()->dateTimeBetween('-80 years', '-18 years')->format('Y-m-d'),
            'gender' => fake()->randomElement(Beneficiary::genders()),
            'contact_number' => fake()->numerify('09#########'),
            'civil_status' => fake()->randomElement(Beneficiary::civilStatuses()),
            'category' => fake()->randomElement(Beneficiary::categories()),
            'status' => fake()->randomElement(Beneficiary::statuses()),
            'date_issued' => now()->toDateString(),
            'created_at' => fake()->dateTimeBetween('-12 months', 'now'),
            'updated_at' => now(),
        ];
    }
}
