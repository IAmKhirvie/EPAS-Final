<?php

namespace Tests\Feature\Livewire;

use App\Livewire\UserTable;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UserTableTest extends TestCase
{
    use RefreshDatabase;

    public function test_component_renders_for_admin(): void
    {
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test(UserTable::class)
            ->assertStatus(200)
            ->assertSee('User Management');
    }

    public function test_search_filters_users(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->create(['first_name' => 'UniqueSearchName']);
        User::factory()->create(['first_name' => 'OtherPerson']);

        Livewire::actingAs($admin)
            ->test(UserTable::class)
            ->set('search', 'UniqueSearchName')
            ->assertSee('UniqueSearchName')
            ->assertDontSee('OtherPerson');
    }

    public function test_role_filter_works(): void
    {
        $admin = User::factory()->admin()->create();
        $student = User::factory()->student()->create(['first_name' => 'StudentUser']);
        $instructor = User::factory()->instructor()->create(['first_name' => 'InstructorUser']);

        Livewire::actingAs($admin)
            ->test(UserTable::class)
            ->set('roleFilter', 'student')
            ->assertSee('StudentUser')
            ->assertDontSee('InstructorUser');
    }

    public function test_status_filter_works(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->create(['first_name' => 'ActiveUser', 'stat' => 1]);
        User::factory()->create(['first_name' => 'PendingUser', 'stat' => 0]);

        Livewire::actingAs($admin)
            ->test(UserTable::class)
            ->set('statusFilter', 'active')
            ->assertSee('ActiveUser');
    }

    public function test_sorting_toggles_direction(): void
    {
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test(UserTable::class)
            ->call('sortBy', 'email')
            ->assertSet('sortField', 'email')
            ->assertSet('sortDirection', 'asc')
            ->call('sortBy', 'email')
            ->assertSet('sortDirection', 'desc');
    }

    public function test_pagination_resets_on_search(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->count(25)->create();

        Livewire::actingAs($admin)
            ->test(UserTable::class)
            ->set('search', 'test')
            ->assertStatus(200);
    }

    public function test_approve_user_sets_stat_to_1(): void
    {
        $admin = User::factory()->admin()->create();
        $pending = User::factory()->create(['stat' => 0]);

        Livewire::actingAs($admin)
            ->test(UserTable::class)
            ->call('approveUser', $pending->id);

        $this->assertEquals(1, $pending->fresh()->stat);
    }

    public function test_cannot_delete_own_account(): void
    {
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test(UserTable::class)
            ->call('deleteUser', $admin->id)
            ->assertSee('You cannot delete your own account.');
    }

    public function test_route_role_filter_scopes_to_role(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->student()->create(['first_name' => 'StudentOnly']);
        User::factory()->instructor()->create(['first_name' => 'InstructorOnly']);

        Livewire::actingAs($admin)
            ->test(UserTable::class, ['routeRoleFilter' => 'student', 'pageTitle' => 'Students'])
            ->assertSee('StudentOnly')
            ->assertDontSee('InstructorOnly')
            ->assertSee('Students');
    }
}
