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
        Schema::create('module_assessment_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained('modules')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('attempt_number')->default(1);
            $table->integer('score')->default(0); // Points earned
            $table->integer('total_points')->default(0); // Total possible points
            $table->decimal('percentage', 5, 2)->default(0); // Score percentage
            $table->boolean('passed')->default(false);
            $table->json('answers')->nullable(); // User's answers
            $table->json('question_ids')->nullable(); // Questions shown (for randomized)
            $table->json('grading_details')->nullable(); // Detailed grading info per question
            $table->integer('time_taken')->nullable(); // in seconds
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->enum('status', ['in_progress', 'completed', 'timed_out', 'abandoned'])->default('in_progress');
            $table->timestamps();
            $table->softDeletes();

            // Indexes for common queries
            $table->index(['module_id', 'user_id']);
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('module_assessment_submissions');
    }
};
