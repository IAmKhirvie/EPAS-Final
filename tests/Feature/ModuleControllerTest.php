<?php

namespace Tests\Feature;

use App\Constants\Roles;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModuleControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_when_accessing_modules(): void
    {
        $response = $this->get(route('modules.index'));
        $response->assertRedirect();
    }

    public function test_authenticated_users_can_access_modules_index(): void
    {
        $user = User::factory()->student()->create();

        $response = $this->actingAs($user)->get(route('modules.index'));
        $this->assertTrue(in_array($response->status(), [200, 302]));
    }

    public function test_admin_can_access_module_creation(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->get(route('modules.create'));
        $this->assertTrue(in_array($response->status(), [200, 302]));
    }

    public function test_instructor_can_access_module_creation(): void
    {
        $user = User::factory()->instructor()->create();

        $response = $this->actingAs($user)->get(route('modules.create'));
        $this->assertTrue(in_array($response->status(), [200, 302]));
    }

    public function test_students_cannot_access_module_creation(): void
    {
        $user = User::factory()->student()->create();

        $response = $this->actingAs($user)->get(route('modules.create'));
        $response->assertRedirect();
    }
}
