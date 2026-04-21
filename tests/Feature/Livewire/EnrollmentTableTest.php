<?php

namespace Tests\Feature\Livewire;

use App\Livewire\EnrollmentTable;
use App\Models\EnrollmentRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EnrollmentTableTest extends TestCase
{
    use RefreshDatabase;

    public function test_component_renders_for_admin(): void
    {
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test(EnrollmentTable::class)
            ->assertStatus(200)
            ->assertSee('Enrollment Requests');
    }

    public function test_status_tabs_show_counts(): void
    {
        $admin = User::factory()->admin()->create();
        $instructor = User::factory()->instructor()->create();
        $student = User::factory()->student()->create();

        EnrollmentRequest::factory()->count(2)->create([
            'instructor_id' => $instructor->id,
            'student_id' => $student->id,
            'status' => 'pending',
        ]);

        Livewire::actingAs($admin)
            ->test(EnrollmentTable::class)
            ->assertSee('2');
    }

    public function test_status_filter_works(): void
    {
        $admin = User::factory()->admin()->create();
        $instructor = User::factory()->instructor()->create();
        $student1 = User::factory()->student()->create(['first_name' => 'PendingStudent', 'last_name' => 'One']);
        $student2 = User::factory()->student()->create(['first_name' => 'ApprovedStudent', 'last_name' => 'Two']);

        EnrollmentRequest::factory()->create([
            'instructor_id' => $instructor->id,
            'student_id' => $student1->id,
            'student_name' => 'PendingStudent One',
            'status' => 'pending',
        ]);
        EnrollmentRequest::factory()->create([
            'instructor_id' => $instructor->id,
            'student_id' => $student2->id,
            'student_name' => 'ApprovedStudent Two',
            'status' => 'approved',
            'processed_by' => $admin->id,
            'processed_at' => now(),
        ]);

        Livewire::actingAs($admin)
            ->test(EnrollmentTable::class)
            ->set('statusFilter', 'pending')
            ->assertSee('PendingStudent');
    }

    public function test_instructor_sees_only_own_requests(): void
    {
        $instructor = User::factory()->instructor()->create();
        $otherInstructor = User::factory()->instructor()->create();
        $student1 = User::factory()->student()->create(['first_name' => 'MyRequestStudent', 'last_name' => 'One']);
        $student2 = User::factory()->student()->create(['first_name' => 'OtherRequestStudent', 'last_name' => 'Two']);

        EnrollmentRequest::factory()->create([
            'instructor_id' => $instructor->id,
            'student_id' => $student1->id,
            'student_name' => 'MyRequestStudent One',
            'status' => 'pending',
        ]);
        EnrollmentRequest::factory()->create([
            'instructor_id' => $otherInstructor->id,
            'student_id' => $student2->id,
            'student_name' => 'OtherRequestStudent Two',
            'status' => 'pending',
        ]);

        Livewire::actingAs($instructor)
            ->test(EnrollmentTable::class)
            ->assertSee('MyRequestStudent')
            ->assertDontSee('OtherRequestStudent');
    }

    public function test_sorting_works(): void
    {
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test(EnrollmentTable::class)
            ->call('sortBy', 'section')
            ->assertSet('sortField', 'section')
            ->assertSet('sortDirection', 'asc');
    }
}
