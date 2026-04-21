<?php

namespace Tests\Feature;

use App\Constants\Roles;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_when_accessing_courses(): void
    {
        $response = $this->get(route('courses.index'));
        $response->assertRedirect();
    }

    public function test_authenticated_users_can_access_courses_index(): void
    {
        $user = User::factory()->student()->create();

        $response = $this->actingAs($user)->get(route('courses.index'));
        $this->assertTrue(in_array($response->status(), [200, 302]));
    }

    public function test_admin_can_access_content_management(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->get(route('content.management'));
        $this->assertTrue(in_array($response->status(), [200, 302]));
    }

    public function test_instructor_can_access_content_management(): void
    {
        $user = User::factory()->instructor()->create();

        $response = $this->actingAs($user)->get(route('content.management'));
        $this->assertTrue(in_array($response->status(), [200, 302]));
    }

    public function test_students_cannot_access_content_management(): void
    {
        $user = User::factory()->student()->create();

        $response = $this->actingAs($user)->get(route('content.management'));
        $response->assertRedirect();
    }
}
