<?php

namespace Tests\Feature;

use App\Models\Beneficiary;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BeneficiaryManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admins_can_register_beneficiaries_and_generate_ids(): void
    {
        Storage::fake('public');

        $user = User::factory()->superAdmin()->create();

        $response = $this->actingAs($user)->post(route('beneficiaries.store'), [
            'first_name' => 'Maria',
            'middle_name' => 'Lopez',
            'last_name' => 'Santos',
            'address' => 'Purok 1, Municipal Road',
            'barangay' => 'Barangay 3',
            'birthdate' => '1964-05-14',
            'gender' => 'Female',
            'contact_number' => '09171234567',
            'civil_status' => 'Widowed',
            'valid_id' => UploadedFile::fake()->image('valid-id.jpg'),
            'photo' => UploadedFile::fake()->image('photo.jpg'),
            'category' => 'Senior Citizen',
            'status' => 'active',
            'supporting_documents' => [
                UploadedFile::fake()->create('certificate.pdf', 120, 'application/pdf'),
            ],
        ]);

        $beneficiary = Beneficiary::query()->first();

        $response->assertRedirect(route('beneficiaries.show', $beneficiary));
        $this->assertNotNull($beneficiary->beneficiary_number);
        $this->assertNotNull($beneficiary->qr_token);
        $this->assertSame('Maria Lopez Santos', $beneficiary->full_name);
        Storage::disk('public')->assertExists($beneficiary->valid_id_path);
        Storage::disk('public')->assertExists($beneficiary->photo_path);
        $this->assertDatabaseHas('beneficiary_documents', [
            'beneficiary_id' => $beneficiary->id,
        ]);
    }

    public function test_beneficiary_create_form_uses_split_name_fields(): void
    {
        $user = User::factory()->superAdmin()->create();

        $this->actingAs($user)
            ->get(route('beneficiaries.create'))
            ->assertOk()
            ->assertSee('First Name')
            ->assertSee('Middle Name')
            ->assertSee('Last Name');
    }

    public function test_beneficiary_records_can_be_filtered_by_barangay(): void
    {
        $user = User::factory()->create();

        $barangayOne = Beneficiary::factory()->create(['barangay' => 'Barangay 1']);
        $barangayTwo = Beneficiary::factory()->create(['barangay' => 'Barangay 2']);

        $this->actingAs($user)
            ->get(route('beneficiaries.index', ['barangay' => 'Barangay 2']))
            ->assertOk()
            ->assertSee($barangayTwo->beneficiary_number)
            ->assertDontSee($barangayOne->beneficiary_number);
    }

    public function test_admins_can_open_a_single_beneficiary_id_card(): void
    {
        $user = User::factory()->superAdmin()->create();
        $beneficiary = Beneficiary::factory()->create();

        $this->actingAs($user)
            ->get(route('beneficiaries.id-card', $beneficiary))
            ->assertOk()
            ->assertSee($beneficiary->beneficiary_number);
    }

    public function test_admins_can_download_a_single_beneficiary_id_card_pdf(): void
    {
        $user = User::factory()->superAdmin()->create();
        $beneficiary = Beneficiary::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('beneficiaries.id-card.pdf', $beneficiary));

        $response->assertOk();
        $this->assertStringContainsString('.pdf', $response->headers->get('content-disposition', ''));
    }

    public function test_admins_can_export_beneficiary_records_as_pdf_and_excel(): void
    {
        $user = User::factory()->superAdmin()->create();
        Beneficiary::factory()->count(2)->create();

        $pdfResponse = $this->actingAs($user)
            ->get(route('beneficiaries.export', 'pdf'));

        $pdfResponse->assertOk();
        $this->assertStringContainsString('.pdf', $pdfResponse->headers->get('content-disposition', ''));

        $excelResponse = $this->actingAs($user)
            ->get(route('beneficiaries.export', 'xlsx'));

        $excelResponse->assertOk();
        $this->assertStringContainsString('.xlsx', $excelResponse->headers->get('content-disposition', ''));
    }
}
