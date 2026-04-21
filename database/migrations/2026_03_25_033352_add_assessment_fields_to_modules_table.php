<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            // Final Assessment settings
            $table->boolean('require_final_assessment')->default(false)->after('is_active');
            $table->boolean('assessment_randomize_questions')->default(true)->after('require_final_assessment');
            $table->boolean('assessment_show_answers')->default(false)->after('assessment_randomize_questions');
            $table->integer('assessment_passing_score')->default(70)->after('assessment_show_answers');
            $table->integer('assessment_time_limit')->nullable()->after('assessment_passing_score'); // in minutes
            $table->integer('assessment_max_attempts')->nullable()->after('assessment_time_limit');
            $table->integer('assessment_question_count')->nullable()->after('assessment_max_attempts'); // null = all questions
            $table->enum('assessment_question_mode', ['all', 'random_subset'])->default('all')->after('assessment_question_count');
            // Configurable question sources (JSON array of types to include)
            $table->json('assessment_include_sources')->nullable()->after('assessment_question_mode');
            // Require completion of all activities before assessment
            $table->boolean('assessment_require_completion')->default(true)->after('assessment_include_sources');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->dropColumn([
                'require_final_assessment',
                'assessment_randomize_questions',
                'assessment_show_answers',
                'assessment_passing_score',
                'assessment_time_limit',
                'assessment_max_attempts',
                'assessment_question_count',
                'assessment_question_mode',
                'assessment_include_sources',
                'assessment_require_completion',
            ]);
        });
    }
};
