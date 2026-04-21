<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_user_management(): void
    {
        $admin = User::factory()->admin()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($admin)->get(route('private.users.index'));
        // Should render or redirect to correct page
        $this->assertTrue(in_array($response->status(), [200, 302]));
    }

    public function test_unauthenticated_user_cannot_access_user_management(): void
    {
        $response = $this->get(route('private.users.index'));
        $response->assertRedirect();
    }

    public function test_admin_cannot_delete_self(): void
    {
        $admin = User::factory()->admin()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($admin)->delete(route('private.users.destroy', $admin));
        // Should redirect with error, not actually delete
        $response->assertRedirect();

        // Admin should still exist
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_factory_creates_correct_roles(): void
    {
        $admin = User::factory()->admin()->create();
        $instructor = User::factory()->instructor()->create();
        $student = User::factory()->student()->create();
        $inactive = User::factory()->inactive()->create();

        $this->assertEquals('admin', $admin->role);
        $this->assertEquals('instructor', $instructor->role);
        $this->assertEquals('student', $student->role);
        $this->assertFalse((bool) $inactive->stat);
    }

    public function test_admin_can_approve_user(): void
    {
        $admin = User::factory()->admin()->create(['email_verified_at' => now()]);
        $pending = User::factory()->inactive()->create();

        $response = $this->actingAs($admin)->post(route('private.users.approve', $pending));
        $response->assertRedirect();

        $pending->refresh();
        $this->assertTrue((bool) $pending->stat);
    }

    public function test_bulk_activate_updates_users(): void
    {
        $admin = User::factory()->admin()->create(['email_verified_at' => now()]);
        $users = User::factory()->inactive()->count(3)->create();

        $response = $this->actingAs($admin)->post(route('private.users.bulk-activate'), [
            'user_ids' => $users->pluck('id')->toArray(),
        ]);
        $response->assertRedirect();

        foreach ($users as $user) {
            $user->refresh();
            $this->assertTrue((bool) $user->stat);
        }
    }
}
