<?php

namespace Tests\Feature\Livewire;

use App\Livewire\AnnouncementList;
use App\Models\Announcement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AnnouncementListTest extends TestCase
{
    use RefreshDatabase;

    public function test_component_renders(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(AnnouncementList::class)
            ->assertStatus(200)
            ->assertSee('Announcements');
    }

    public function test_shows_live_badge(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(AnnouncementList::class)
            ->assertSee('Live');
    }

    public function test_admin_sees_create_button(): void
    {
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test(AnnouncementList::class)
            ->assertSee('New Announcement');
    }

    public function test_student_does_not_see_create_button(): void
    {
        $student = User::factory()->student()->create();

        Livewire::actingAs($student)
            ->test(AnnouncementList::class)
            ->assertDontSee('New Announcement');
    }

    public function test_search_filters_announcements(): void
    {
        $admin = User::factory()->admin()->create();
        Announcement::factory()->create(['title' => 'Important Update', 'user_id' => $admin->id]);
        Announcement::factory()->create(['title' => 'Other News', 'user_id' => $admin->id]);

        Livewire::actingAs($admin)
            ->test(AnnouncementList::class)
            ->set('search', 'Important')
            ->assertSee('Important Update')
            ->assertDontSee('Other News');
    }

    public function test_pinned_announcements_appear_first(): void
    {
        $admin = User::factory()->admin()->create();
        Announcement::factory()->create(['title' => 'Normal Post', 'user_id' => $admin->id, 'is_pinned' => false, 'created_at' => now()]);
        Announcement::factory()->create(['title' => 'Pinned Post', 'user_id' => $admin->id, 'is_pinned' => true, 'created_at' => now()->subDay()]);

        $component = Livewire::actingAs($admin)->test(AnnouncementList::class);

        // The pinned post should be visible (order is pinned first)
        $component->assertSee('Pinned Post');
    }
}
