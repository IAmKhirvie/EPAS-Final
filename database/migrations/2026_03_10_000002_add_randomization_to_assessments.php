<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds randomization options to self-checks, task sheets, and job sheets.
     */
    public function up(): void
    {
        // Add randomization to self_checks (quizzes)
        Schema::table('self_checks', function (Blueprint $table) {
            $table->boolean('randomize_questions')->default(false)->after('reveal_answers');
            $table->boolean('randomize_options')->default(false)->after('randomize_questions');
        });

        // Add randomization to task_sheets
        Schema::table('task_sheets', function (Blueprint $table) {
            $table->boolean('randomize_items')->default(false)->after('difficulty_level');
        });

        // Add randomization to job_sheets
        Schema::table('job_sheets', function (Blueprint $table) {
            $table->boolean('randomize_steps')->default(false)->after('difficulty_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('self_checks', function (Blueprint $table) {
            $table->dropColumn(['randomize_questions', 'randomize_options']);
        });

        Schema::table('task_sheets', function (Blueprint $table) {
            $table->dropColumn('randomize_items');
        });

        Schema::table('job_sheets', function (Blueprint $table) {
            $table->dropColumn('randomize_steps');
        });
    }
};
