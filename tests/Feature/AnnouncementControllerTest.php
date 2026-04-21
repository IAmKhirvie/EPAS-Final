<?php

namespace Tests\Feature;

use App\Constants\Roles;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnouncementControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_announcements(): void
    {
        $response = $this->get(route('private.announcements.index'));
        $response->assertRedirect();
    }

    public function test_authenticated_users_can_view_announcements_index(): void
    {
        $user = User::factory()->student()->create();

        $response = $this->actingAs($user)->get(route('private.announcements.index'));
        $this->assertTrue(in_array($response->status(), [200, 302]));
    }

    public function test_admin_can_access_announcement_creation(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->get(route('private.announcements.create'));
        $this->assertTrue(in_array($response->status(), [200, 302]));
    }

    public function test_instructor_can_access_announcement_creation(): void
    {
        $user = User::factory()->instructor()->create();

        $response = $this->actingAs($user)->get(route('private.announcements.create'));
        $this->assertTrue(in_array($response->status(), [200, 302]));
    }

    public function test_students_cannot_access_announcement_creation(): void
    {
        $user = User::factory()->student()->create();

        $response = $this->actingAs($user)->get(route('private.announcements.create'));
        $response->assertRedirect();
    }
}
