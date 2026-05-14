<?php

namespace Tests\Feature;

use App\Models\AssistanceLog;
use App\Models\Beneficiary;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_exposes_summary_metrics(): void
    {
        $user = User::factory()->superAdmin()->create();

        $active = Beneficiary::factory()->create([
            'status' => Beneficiary::STATUS_ACTIVE,
            'gender' => 'Female',
            'category' => 'Senior Citizen',
            'barangay' => 'Barangay 1',
        ]);

        Beneficiary::factory()->create([
            'status' => Beneficiary::STATUS_INACTIVE,
            'gender' => 'Male',
            'category' => 'PWD',
            'barangay' => 'Barangay 2',
        ]);

        AssistanceLog::factory()->create([
            'beneficiary_id' => $active->id,
            'amount' => 1500,
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('summary', fn (array $summary) => $summary['totalBeneficiaries'] === 2 && $summary['activeBeneficiaries'] === 1 && $summary['inactiveBeneficiaries'] === 1);
    }

    public function test_dashboard_exposes_analytics_chart_configurations(): void
    {
        $user = User::factory()->superAdmin()->create();

        $beneficiary = Beneficiary::factory()->create([
            'status' => Beneficiary::STATUS_ACTIVE,
            'gender' => 'Female',
            'category' => 'Senior Citizen',
            'barangay' => 'Barangay 1',
        ]);

        AssistanceLog::factory()->create([
            'beneficiary_id' => $beneficiary->id,
            'amount' => 2000,
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('charts', fn (array $charts) => isset($charts['registration'], $charts['status'], $charts['barangay'], $charts['gender'], $charts['age'], $charts['priority'], $charts['assistance'])
                && $charts['status']['type'] === 'doughnut'
                && $charts['barangay']['type'] === 'pie'
                && $charts['assistance']['type'] === 'bar');
    }
}
