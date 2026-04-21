<?php

namespace Tests\Unit;

use App\Models\HomeworkSubmission;
use App\Models\JobSheetSubmission;
use App\Models\SelfCheckSubmission;
use App\Models\TaskSheetSubmission;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tests\TestCase;

class SubmissionModelTest extends TestCase
{
    /**
     * The 4 submission model classes to test.
     */
    private array $submissionModels = [
        HomeworkSubmission::class,
        SelfCheckSubmission::class,
        TaskSheetSubmission::class,
        JobSheetSubmission::class,
    ];

    // ──────────────────────────────────────────────
    // SoftDeletes trait usage tests
    // ──────────────────────────────────────────────

    public function test_homework_submission_uses_soft_deletes(): void
    {
        $this->assertContains(
            SoftDeletes::class,
            class_uses_recursive(HomeworkSubmission::class)
        );
    }

    public function test_self_check_submission_uses_soft_deletes(): void
    {
        $this->assertContains(
            SoftDeletes::class,
            class_uses_recursive(SelfCheckSubmission::class)
        );
    }

    public function test_task_sheet_submission_uses_soft_deletes(): void
    {
        $this->assertContains(
            SoftDeletes::class,
            class_uses_recursive(TaskSheetSubmission::class)
        );
    }

    public function test_job_sheet_submission_uses_soft_deletes(): void
    {
        $this->assertContains(
            SoftDeletes::class,
            class_uses_recursive(JobSheetSubmission::class)
        );
    }

    // ──────────────────────────────────────────────
    // deleted_at cast tests
    // ──────────────────────────────────────────────

    public function test_homework_submission_casts_deleted_at(): void
    {
        $model = new HomeworkSubmission();
        $casts = $model->getCasts();

        $this->assertArrayHasKey('deleted_at', $casts);
    }

    public function test_self_check_submission_casts_deleted_at(): void
    {
        $model = new SelfCheckSubmission();
        $casts = $model->getCasts();

        $this->assertArrayHasKey('deleted_at', $casts);
    }

    public function test_task_sheet_submission_casts_deleted_at(): void
    {
        $model = new TaskSheetSubmission();
        $casts = $model->getCasts();

        $this->assertArrayHasKey('deleted_at', $casts);
    }

    public function test_job_sheet_submission_casts_deleted_at(): void
    {
        $model = new JobSheetSubmission();
        $casts = $model->getCasts();

        $this->assertArrayHasKey('deleted_at', $casts);
    }
}
