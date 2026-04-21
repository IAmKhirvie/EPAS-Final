<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Expands the question_type column to support new interactive question types:
     * - multiple_select: Multiple correct answers (checkboxes)
     * - numeric: Number with tolerance
     * - classification: Sort items into categories
     * - image_identification: "Name this picture"
     * - hotspot: Click on image area
     * - image_labeling: Label parts of diagram
     * - audio_question: Listen and answer
     * - video_question: Watch and answer
     * - drag_drop: Drag items to zones
     * - slider: Numeric slider range
     *
     * Compatible with: MySQL 5.7+, MariaDB 10.2+, XAMPP PHP 8.2
     */
    public function up(): void
    {
        // Check if table exists first (for fresh installations)
        if (!Schema::hasTable('self_check_questions')) {
            return;
        }

        // Change enum to varchar to support all question types
        // Works on both MySQL and MariaDB (XAMPP default)
        try {
            DB::statement("ALTER TABLE `self_check_questions` MODIFY COLUMN `question_type` VARCHAR(50) NOT NULL");
        } catch (\Exception $e) {
            // Column might already be VARCHAR, ignore error
        }

        // Add media column for audio/video URLs
        if (!Schema::hasColumn('self_check_questions', 'media')) {
            Schema::table('self_check_questions', function (Blueprint $table) {
                $table->text('media')->nullable()->after('explanation');
            });
        }

        // Add metadata column for type-specific settings
        if (!Schema::hasColumn('self_check_questions', 'metadata')) {
            Schema::table('self_check_questions', function (Blueprint $table) {
                $table->text('metadata')->nullable()->after('explanation');
            });
        }

        // Add image column for question images (already may exist from other migrations)
        if (!Schema::hasColumn('self_check_questions', 'image')) {
            Schema::table('self_check_questions', function (Blueprint $table) {
                $table->string('image', 500)->nullable()->after('question_text');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('self_check_questions')) {
            return;
        }

        // Note: Reverting to enum will fail if data contains new types
        // Only run this if you're sure no new question types exist
        try {
            DB::statement("ALTER TABLE `self_check_questions` MODIFY COLUMN `question_type` ENUM('multiple_choice', 'true_false', 'identification', 'essay', 'matching', 'enumeration') NOT NULL");
        } catch (\Exception $e) {
            // Ignore if it fails
        }

        Schema::table('self_check_questions', function (Blueprint $table) {
            if (Schema::hasColumn('self_check_questions', 'media')) {
                $table->dropColumn('media');
            }
            if (Schema::hasColumn('self_check_questions', 'metadata')) {
                $table->dropColumn('metadata');
            }
        });
    }
};
