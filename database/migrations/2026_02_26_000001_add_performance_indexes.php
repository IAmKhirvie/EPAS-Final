<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->index('last_login', 'users_last_login_idx');
            $table->index('section', 'users_section_idx');
        });

        Schema::table('user_progress', function (Blueprint $table) {
            $table->index(['status', 'completed_at'], 'user_progress_status_completed_idx');
        });

        Schema::table('homework_submissions', function (Blueprint $table) {
            $table->index(['user_id', 'evaluated_at'], 'hw_sub_user_eval_idx');
        });

        Schema::table('job_sheet_submissions', function (Blueprint $table) {
            $table->index(['user_id', 'evaluated_at'], 'js_sub_user_eval_idx');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_last_login_idx');
            $table->dropIndex('users_section_idx');
        });

        Schema::table('user_progress', function (Blueprint $table) {
            $table->dropIndex('user_progress_status_completed_idx');
        });

        Schema::table('homework_submissions', function (Blueprint $table) {
            $table->dropIndex('hw_sub_user_eval_idx');
        });

        Schema::table('job_sheet_submissions', function (Blueprint $table) {
            $table->dropIndex('js_sub_user_eval_idx');
        });
    }
};
