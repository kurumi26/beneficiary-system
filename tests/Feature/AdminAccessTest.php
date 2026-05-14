<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login_from_dashboard(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
    }

    public function test_authenticated_admins_can_access_the_dashboard(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create())
            ->get('/dashboard')
            ->assertOk();
    }

    public function test_non_admins_receive_forbidden_when_accessing_dashboard(): void
    {
        $this->actingAs(User::factory()->create([
            'role' => 'beneficiary',
        ]))
            ->get('/dashboard')
            ->assertForbidden();
    }
}
