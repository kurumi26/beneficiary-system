<?php

namespace Database\Factories;

use App\Models\BeneficiaryDocument;
use App\Models\Beneficiary;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BeneficiaryDocument>
 */
class BeneficiaryDocumentFactory extends Factory
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
            'title' => fake()->randomElement(['Residence Certificate', 'Barangay Clearance', 'Medical Record']),
            'file_path' => 'beneficiaries/documents/'.fake()->uuid().'.pdf',
        ];
    }
}
