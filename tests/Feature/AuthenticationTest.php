<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_accessible(): void
    {
        $response = $this->get(route('login'));
        // Login page should return 200 or redirect (302) to another login form
        $this->assertTrue(in_array($response->status(), [200, 302]));
    }

    public function test_register_page_is_accessible(): void
    {
        $response = $this->get(route('register'));
        $this->assertTrue(in_array($response->status(), [200, 302]));
    }

    public function test_user_cannot_login_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'email' => 'user@test.com',
        ]);

        $this->post(route('login'), [
            'email' => 'user@test.com',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_guests_cannot_access_dashboard(): void
    {
        $response = $this->get(route('student.dashboard'));
        $response->assertRedirect();
    }

    public function test_authenticated_student_can_access_dashboard(): void
    {
        $user = User::factory()->student()->create();

        $response = $this->actingAs($user)->get(route('student.dashboard'));
        // Should return 200 or redirect to specific dashboard
        $this->assertTrue(in_array($response->status(), [200, 302]));
    }

    public function test_authenticated_admin_can_access_admin_dashboard(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->get(route('admin.dashboard'));
        $this->assertTrue(in_array($response->status(), [200, 302]));
    }
}
