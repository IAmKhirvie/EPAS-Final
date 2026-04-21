<?php

namespace Tests\Feature\Livewire;

use App\Livewire\AuditLogTable;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AuditLogTableTest extends TestCase
{
    use RefreshDatabase;

    public function test_component_renders_for_admin(): void
    {
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test(AuditLogTable::class)
            ->assertStatus(200)
            ->assertSee('Audit Logs');
    }

    public function test_search_filters_logs(): void
    {
        $admin = User::factory()->admin()->create();
        AuditLog::factory()->create(['description' => 'Unique audit event', 'user_id' => $admin->id]);
        AuditLog::factory()->create(['description' => 'Other event', 'user_id' => $admin->id]);

        Livewire::actingAs($admin)
            ->test(AuditLogTable::class)
            ->set('search', 'Unique audit')
            ->assertSee('Unique audit event')
            ->assertDontSee('Other event');
    }

    public function test_action_filter_works(): void
    {
        $admin = User::factory()->admin()->create();
        AuditLog::factory()->create(['action' => 'login', 'user_id' => $admin->id]);
        AuditLog::factory()->create(['action' => 'delete', 'user_id' => $admin->id]);

        Livewire::actingAs($admin)
            ->test(AuditLogTable::class)
            ->set('actionFilter', 'login')
            ->assertSee('Login');
    }

    public function test_expand_toggle_works(): void
    {
        $admin = User::factory()->admin()->create();
        $log = AuditLog::factory()->create([
            'user_id' => $admin->id,
            'old_values' => ['field' => 'old'],
            'new_values' => ['field' => 'new'],
        ]);

        Livewire::actingAs($admin)
            ->test(AuditLogTable::class)
            ->call('toggleExpand', $log->id)
            ->assertSet('expandedLogId', $log->id)
            ->call('toggleExpand', $log->id)
            ->assertSet('expandedLogId', null);
    }

    public function test_date_range_filter_works(): void
    {
        $admin = User::factory()->admin()->create();
        AuditLog::factory()->create([
            'user_id' => $admin->id,
            'created_at' => now()->subDays(5),
        ]);

        Livewire::actingAs($admin)
            ->test(AuditLogTable::class)
            ->set('dateFrom', now()->subDays(10)->toDateString())
            ->set('dateTo', now()->toDateString())
            ->assertStatus(200);
    }
}
