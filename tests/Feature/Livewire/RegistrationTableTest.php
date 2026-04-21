<?php

namespace Tests\Feature\Livewire;

use App\Livewire\RegistrationTable;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RegistrationTableTest extends TestCase
{
    use RefreshDatabase;

    public function test_component_renders_for_admin(): void
    {
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test(RegistrationTable::class)
            ->assertStatus(200)
            ->assertSee('Registration Management');
    }

    public function test_status_tabs_show_counts(): void
    {
        $admin = User::factory()->admin()->create();
        Registration::factory()->count(3)->create(['status' => 'pending']);
        Registration::factory()->count(2)->emailVerified()->create();

        Livewire::actingAs($admin)
            ->test(RegistrationTable::class)
            ->assertSee('3')
            ->assertSee('2');
    }

    public function test_search_filters_registrations(): void
    {
        $admin = User::factory()->admin()->create();
        Registration::factory()->create(['first_name' => 'SearchableReg', 'status' => 'pending']);
        Registration::factory()->create(['first_name' => 'OtherReg', 'status' => 'pending']);

        Livewire::actingAs($admin)
            ->test(RegistrationTable::class)
            ->set('search', 'SearchableReg')
            ->assertSee('SearchableReg')
            ->assertDontSee('OtherReg');
    }

    public function test_status_filter_shows_correct_registrations(): void
    {
        $admin = User::factory()->admin()->create();
        Registration::factory()->create(['first_name' => 'PendingOne', 'status' => 'pending']);
        Registration::factory()->rejected()->create(['first_name' => 'RejectedOne']);

        Livewire::actingAs($admin)
            ->test(RegistrationTable::class)
            ->set('statusFilter', 'rejected')
            ->assertSee('RejectedOne');
    }

    public function test_delete_only_works_on_rejected(): void
    {
        $admin = User::factory()->admin()->create();
        $pending = Registration::factory()->create(['status' => 'pending']);

        Livewire::actingAs($admin)
            ->test(RegistrationTable::class)
            ->set('statusFilter', 'all')
            ->call('deleteRegistration', $pending->id)
            ->assertSee('Only rejected registrations can be deleted.');

        $this->assertNotNull($pending->fresh());
    }

    public function test_sorting_works(): void
    {
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test(RegistrationTable::class)
            ->call('sortBy', 'email')
            ->assertSet('sortField', 'email')
            ->assertSet('sortDirection', 'asc');
    }
}
