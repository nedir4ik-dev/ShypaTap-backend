<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;
    public function test_patient_can_register_and_is_always_patient(): void
    {
        $response = $this->postJson(
            '/api/register',
            [
                'name' => 'Synag Näsag',
                'email' =>
                'patient@example.test',
                'password' => 'Password123',
                'password_confirmation' => 'Password123'
            ]
        );
        $response->assertCreated()->assertJsonPath('user.role', 'patient');
    }
    public function test_wrong_password_is_rejected(): void
    {
        User::factory()->create(
            [
                'email' => 'patient@example.test',
                'password' => 'Password123'
            ]
        );
        $this->postJson(
            '/api/login',
            [
                'email' => 'patient@example.test',
                'password' => 'wrong-password'
            ]
        )->assertUnauthorized();
    }
    public function test_patient_cannot_access_admin_api(): void
    {
        $user = User::factory()->create(['role' => 'patient']);
        $this->actingAs($user)->getJson('/api/admin/dashboard')->assertForbidden();
    }
}
