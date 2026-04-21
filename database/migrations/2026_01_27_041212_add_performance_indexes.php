<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add indexes to user_progress for faster queries
        if (Schema::hasTable('user_progress')) {
            Schema::table('user_progress', function (Blueprint $table) {
                $table->index(['user_id', 'status'], 'user_progress_user_status_index');
                $table->index(['module_id', 'status'], 'user_progress_module_status_index');
            });
        }

        // Add indexes to announcements
        if (Schema::hasTable('announcements')) {
            Schema::table('announcements', function (Blueprint $table) {
                $table->index('created_at', 'announcements_created_at_index');
                $table->index(['is_pinned', 'created_at'], 'announcements_pinned_created_index');
            });
        }

        // Add indexes to users for common queries
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->index(['role', 'stat'], 'users_role_stat_index');
            });
        }

        // Add indexes to modules
        if (Schema::hasTable('modules') && Schema::hasColumn('modules', 'course_id')) {
            Schema::table('modules', function (Blueprint $table) {
                $table->index(['course_id', 'is_active'], 'modules_course_active_index');
            });
        }

        // Add indexes to homework_submissions
        if (Schema::hasTable('homework_submissions')) {
            Schema::table('homework_submissions', function (Blueprint $table) {
                $table->index(['user_id', 'created_at'], 'homework_submissions_user_created_index');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('user_progress')) {
            Schema::table('user_progress', function (Blueprint $table) {
                $table->dropIndex('user_progress_user_status_index');
                $table->dropIndex('user_progress_module_status_index');
            });
        }

        if (Schema::hasTable('announcements')) {
            Schema::table('announcements', function (Blueprint $table) {
                $table->dropIndex('announcements_created_at_index');
                $table->dropIndex('announcements_pinned_created_index');
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex('users_role_stat_index');
            });
        }

        if (Schema::hasTable('modules')) {
            Schema::table('modules', function (Blueprint $table) {
                $table->dropIndex('modules_course_active_index');
            });
        }

        if (Schema::hasTable('homework_submissions')) {
            Schema::table('homework_submissions', function (Blueprint $table) {
                $table->dropIndex('homework_submissions_user_created_index');
            });
        }
    }
};
