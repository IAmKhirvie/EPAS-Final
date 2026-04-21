<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Composite index for homework submission lookups
        if (Schema::hasTable('homework_submissions')) {
            Schema::table('homework_submissions', function (Blueprint $table) {
                $table->index(['homework_id', 'user_id'], 'hw_sub_homework_user_idx');
            });
        }

        // Composite index for self-check submission lookups
        if (Schema::hasTable('self_check_submissions')) {
            Schema::table('self_check_submissions', function (Blueprint $table) {
                $table->index(['self_check_id', 'user_id'], 'sc_sub_selfcheck_user_idx');
            });
        }

        // Composite index for forum thread queries
        if (Schema::hasTable('forum_threads')) {
            Schema::table('forum_threads', function (Blueprint $table) {
                $table->index(['category_id', 'is_pinned'], 'ft_category_pinned_idx');
            });
        }

        // Composite index for task sheet submission lookups
        if (Schema::hasTable('task_sheet_submissions')) {
            Schema::table('task_sheet_submissions', function (Blueprint $table) {
                $table->index(['task_sheet_id', 'user_id'], 'ts_sub_tasksheet_user_idx');
            });
        }

        // Composite index for job sheet submission lookups
        if (Schema::hasTable('job_sheet_submissions')) {
            Schema::table('job_sheet_submissions', function (Blueprint $table) {
                $table->index(['job_sheet_id', 'user_id'], 'js_sub_jobsheet_user_idx');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('homework_submissions')) {
            Schema::table('homework_submissions', function (Blueprint $table) {
                $table->dropIndex('hw_sub_homework_user_idx');
            });
        }

        if (Schema::hasTable('self_check_submissions')) {
            Schema::table('self_check_submissions', function (Blueprint $table) {
                $table->dropIndex('sc_sub_selfcheck_user_idx');
            });
        }

        if (Schema::hasTable('forum_threads')) {
            Schema::table('forum_threads', function (Blueprint $table) {
                $table->dropIndex('ft_category_pinned_idx');
            });
        }

        if (Schema::hasTable('task_sheet_submissions')) {
            Schema::table('task_sheet_submissions', function (Blueprint $table) {
                $table->dropIndex('ts_sub_tasksheet_user_idx');
            });
        }

        if (Schema::hasTable('job_sheet_submissions')) {
            Schema::table('job_sheet_submissions', function (Blueprint $table) {
                $table->dropIndex('js_sub_jobsheet_user_idx');
            });
        }
    }
};
