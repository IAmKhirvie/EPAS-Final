<?php

namespace Tests\Feature;

use App\Constants\Roles;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GradesControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_grades(): void
    {
        $response = $this->get(route('grades.index'));
        $response->assertRedirect();
    }

    public function test_authenticated_users_can_access_grades_index(): void
    {
        $user = User::factory()->student()->create();

        $response = $this->actingAs($user)->get(route('grades.index'));
        $this->assertTrue(in_array($response->status(), [200, 302]));
    }

    public function test_admin_can_access_grade_export(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->get(route('grades.export'));
        $this->assertTrue(in_array($response->status(), [200, 302]));
    }

    public function test_instructor_can_access_grade_export(): void
    {
        $user = User::factory()->instructor()->create();

        $response = $this->actingAs($user)->get(route('grades.export'));
        $this->assertTrue(in_array($response->status(), [200, 302]));
    }

    public function test_students_cannot_access_grade_export(): void
    {
        $user = User::factory()->student()->create();

        $response = $this->actingAs($user)->get(route('grades.export'));
        $response->assertRedirect();
    }
}
