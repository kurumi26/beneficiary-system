<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Beneficiary;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class QrScannerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admins_can_access_the_qr_scanner_page(): void
    {
        $user = User::factory()->superAdmin()->create();

        $this->actingAs($user)
            ->get(route('qr-scanner.index'))
            ->assertOk()
            ->assertSee('Beneficiary QR Scanner');
    }

    public function test_qr_scanner_verifies_generated_beneficiary_codes_and_logs_them(): void
    {
        $user = User::factory()->superAdmin()->create();
        $beneficiary = Beneficiary::factory()->create([
            'photo_path' => 'beneficiaries/photos/sample-photo.jpg',
        ]);

        $this->actingAs($user)
            ->postJson(route('qr-scanner.verify'), [
                'payload' => route('beneficiaries.verification', $beneficiary->qr_token),
            ])
            ->assertOk()
            ->assertJsonPath('is_legitimate', true)
            ->assertJsonPath('beneficiary.full_name', $beneficiary->full_name)
            ->assertJsonPath('beneficiary.beneficiary_number', $beneficiary->beneficiary_number)
            ->assertJsonPath('beneficiary.photo_url', Storage::url($beneficiary->photo_path));

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'beneficiary.qr_scan_verified',
            'subject_id' => $beneficiary->id,
        ]);
    }

    public function test_qr_scanner_rejects_non_system_codes_and_logs_them(): void
    {
        $user = User::factory()->superAdmin()->create();

        $this->actingAs($user)
            ->postJson(route('qr-scanner.verify'), [
                'payload' => 'https://example.com/not-a-beneficiary-code',
            ])
            ->assertStatus(422)
            ->assertJsonPath('is_legitimate', false)
            ->assertJsonPath('reason', 'not_system_generated')
            ->assertJsonPath('notice_title', 'Warning: Not a legitimate beneficiary QR code.')
            ->assertJsonPath('message', 'The scanned QR code is not a system-generated beneficiary code.');

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'beneficiary.qr_scan_rejected',
        ]);
    }

    public function test_qr_scanner_history_is_paginated_with_two_records_per_page(): void
    {
        $user = User::factory()->superAdmin()->create();

        $oldest = ActivityLog::query()->create([
            'user_id' => $user->id,
            'action' => 'beneficiary.qr_scan_verified',
            'description' => 'Oldest scan entry',
            'properties' => ['beneficiary_number' => 'GBMS-OLD-001'],
        ]);

        $middle = ActivityLog::query()->create([
            'user_id' => $user->id,
            'action' => 'beneficiary.qr_scan_rejected',
            'description' => 'Middle scan entry',
            'properties' => ['reason' => 'invalid_verification_route'],
        ]);

        $latest = ActivityLog::query()->create([
            'user_id' => $user->id,
            'action' => 'beneficiary.qr_scan_verified',
            'description' => 'Latest scan entry',
            'properties' => ['beneficiary_number' => 'GBMS-NEW-001'],
        ]);

        $oldest->forceFill([
            'created_at' => now()->subMinutes(3),
            'updated_at' => now()->subMinutes(3),
        ])->save();

        $middle->forceFill([
            'created_at' => now()->subMinutes(2),
            'updated_at' => now()->subMinutes(2),
        ])->save();

        $latest->forceFill([
            'created_at' => now()->subMinute(),
            'updated_at' => now()->subMinute(),
        ])->save();

        $this->actingAs($user)
            ->get(route('qr-scanner.index'))
            ->assertOk()
            ->assertSee('Latest scan entry')
            ->assertSee('Middle scan entry')
            ->assertDontSee('Oldest scan entry')
            ->assertSee('Page 1 of 2');

        $this->actingAs($user)
            ->get(route('qr-scanner.index', ['history_page' => 2]))
            ->assertOk()
            ->assertSee('Oldest scan entry')
            ->assertDontSee('Latest scan entry')
            ->assertSee('Page 2 of 2');
    }
}
