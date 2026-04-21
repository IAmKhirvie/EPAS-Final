<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Main modules table
        if (!Schema::hasTable('modules')) {
            Schema::create('modules', function (Blueprint $table) {
                $table->id();
                $table->string('sector')->default('Electronics');
                $table->string('qualification_title');
                $table->string('unit_of_competency');
                $table->string('module_title');
                $table->string('module_number');
                $table->string('module_name');
                $table->text('table_of_contents')->nullable();
                $table->text('how_to_use_cblm')->nullable();
                $table->text('introduction')->nullable();
                $table->text('learning_outcomes')->nullable();
                $table->boolean('is_active')->default(true);
                $table->integer('order')->default(0);
                $table->timestamps();
            });
        }

        // Information sheets
        if (!Schema::hasTable('information_sheets')) {
            Schema::create('information_sheets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('module_id')->constrained()->onDelete('cascade');
                $table->string('sheet_number');
                $table->string('title');
                $table->text('content');
                $table->integer('order')->default(0);
                $table->timestamps();
            });
        }

        // Self Checks
        if (!Schema::hasTable('self_checks')) {
            Schema::create('self_checks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('information_sheet_id')->constrained()->onDelete('cascade');
                $table->string('check_number');
                $table->string('title');
                $table->text('description')->nullable();
                $table->text('instructions');
                $table->integer('time_limit')->nullable()->comment('Time limit in minutes');
                $table->integer('passing_score')->default(70);
                $table->integer('total_points')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['information_sheet_id', 'check_number']);
            });
        }

        // Self Check Questions (1NF - separate table for atomic data)
        if (!Schema::hasTable('self_check_questions')) {
            Schema::create('self_check_questions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('self_check_id')->constrained()->onDelete('cascade');
                $table->text('question_text');
                $table->enum('question_type', [
                    'multiple_choice',
                    'true_false',
                    'identification',
                    'essay',
                    'matching',
                    'enumeration'
                ]);
                $table->integer('points')->default(1);
                $table->text('correct_answer')->nullable();
                $table->text('explanation')->nullable();
                $table->integer('order')->default(0);
                $table->timestamps();

                $table->index(['self_check_id', 'order']);
            });
        }

        // Self Check Question Options (1NF - separate table for options instead of JSON)
        if (!Schema::hasTable('self_check_question_options')) {
            Schema::create('self_check_question_options', function (Blueprint $table) {
                $table->id();
                $table->foreignId('question_id')->constrained('self_check_questions')->onDelete('cascade');
                $table->string('option_text');
                $table->char('option_letter', 1);
                $table->integer('order')->default(0);
                $table->timestamps();

                $table->index(['question_id', 'order']);
            });
        }

        // Self Check Submissions
        if (!Schema::hasTable('self_check_submissions')) {
            Schema::create('self_check_submissions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('self_check_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->integer('score')->default(0);
                $table->integer('total_points');
                $table->decimal('percentage', 5, 2)->default(0);
                $table->boolean('passed')->default(false);
                $table->integer('time_taken')->nullable()->comment('Time taken in seconds');
                $table->timestamp('completed_at');
                $table->timestamps();

                $table->index(['self_check_id', 'user_id']);
                $table->index(['user_id', 'completed_at']);
            });
        }

        // Self Check Submission Answers (1NF - separate table for answers)
        if (!Schema::hasTable('self_check_submission_answers')) {
            Schema::create('self_check_submission_answers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('submission_id')->constrained('self_check_submissions')->onDelete('cascade');
                $table->foreignId('question_id')->constrained('self_check_questions')->onDelete('cascade');
                $table->text('answer');
                $table->boolean('is_correct')->nullable();
                $table->timestamps();

                $table->index(['submission_id', 'question_id']);
            });
        }

        // Task Sheets
        if (!Schema::hasTable('task_sheets')) {
            Schema::create('task_sheets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('information_sheet_id')->constrained()->onDelete('cascade');
                $table->string('task_number');
                $table->string('title');
                $table->text('description')->nullable();
                $table->text('instructions');
                $table->string('image_path')->nullable();
                $table->integer('estimated_duration')->nullable()->comment('Estimated duration in minutes');
                $table->enum('difficulty_level', ['beginner', 'intermediate', 'advanced'])->default('beginner');
                $table->timestamps();

                $table->index(['information_sheet_id', 'task_number']);
            });
        }

        // Task Sheet Objectives (1NF - separate table instead of JSON)
        if (!Schema::hasTable('task_sheet_objectives')) {
            Schema::create('task_sheet_objectives', function (Blueprint $table) {
                $table->id();
                $table->foreignId('task_sheet_id')->constrained()->onDelete('cascade');
                $table->text('objective');
                $table->integer('order')->default(0);
                $table->timestamps();

                $table->index(['task_sheet_id', 'order']);
            });
        }

        // Task Sheet Materials (1NF - separate table instead of JSON)
        if (!Schema::hasTable('task_sheet_materials')) {
            Schema::create('task_sheet_materials', function (Blueprint $table) {
                $table->id();
                $table->foreignId('task_sheet_id')->constrained()->onDelete('cascade');
                $table->string('material_name');
                $table->string('quantity')->nullable();
                $table->integer('order')->default(0);
                $table->timestamps();

                $table->index(['task_sheet_id', 'order']);
            });
        }

        // Task Sheet Safety Precautions (1NF - separate table instead of JSON)
        if (!Schema::hasTable('task_sheet_safety_precautions')) {
            Schema::create('task_sheet_safety_precautions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('task_sheet_id')->constrained()->onDelete('cascade');
                $table->text('precaution');
                $table->integer('order')->default(0);
                $table->timestamps();

                $table->index(['task_sheet_id', 'order']);
            });
        }

        // Task Sheet Items
        if (!Schema::hasTable('task_sheet_items')) {
            Schema::create('task_sheet_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('task_sheet_id')->constrained()->onDelete('cascade');
                $table->string('part_name');
                $table->text('description');
                $table->text('expected_finding');
                $table->string('acceptable_range');
                $table->string('image_path')->nullable();
                $table->integer('order')->default(0);
                $table->timestamps();

                $table->index(['task_sheet_id', 'order']);
            });
        }

        // Task Sheet Submissions
        if (!Schema::hasTable('task_sheet_submissions')) {
            Schema::create('task_sheet_submissions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('task_sheet_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->text('observations')->nullable();
                $table->text('challenges')->nullable();
                $table->integer('time_taken')->nullable()->comment('Time taken in minutes');
                $table->timestamp('submitted_at');
                $table->timestamps();

                $table->index(['task_sheet_id', 'user_id']);
                $table->index(['user_id', 'submitted_at']);
            });
        }

        // Task Sheet Submission Findings (1NF - separate table for findings)
        if (!Schema::hasTable('task_sheet_submission_findings')) {
            Schema::create('task_sheet_submission_findings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('submission_id')->constrained('task_sheet_submissions')->onDelete('cascade');
                $table->foreignId('item_id')->constrained('task_sheet_items')->onDelete('cascade');
                $table->text('finding');
                $table->boolean('is_within_range')->nullable();
                $table->timestamps();

                $table->index(['submission_id', 'item_id']);
            });
        }

        // Job Sheets
        if (!Schema::hasTable('job_sheets')) {
            Schema::create('job_sheets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('information_sheet_id')->constrained()->onDelete('cascade');
                $table->string('job_number');
                $table->string('title');
                $table->text('description')->nullable();
                $table->text('procedures');
                $table->text('performance_criteria')->nullable();
                $table->integer('estimated_duration')->nullable()->comment('Estimated duration in minutes');
                $table->enum('difficulty_level', ['beginner', 'intermediate', 'advanced'])->default('beginner');
                $table->timestamps();

                $table->index(['information_sheet_id', 'job_number']);
            });
        }

        // Job Sheet Objectives (1NF - separate table instead of JSON)
        if (!Schema::hasTable('job_sheet_objectives')) {
            Schema::create('job_sheet_objectives', function (Blueprint $table) {
                $table->id();
                $table->foreignId('job_sheet_id')->constrained()->onDelete('cascade');
                $table->text('objective');
                $table->integer('order')->default(0);
                $table->timestamps();

                $table->index(['job_sheet_id', 'order']);
            });
        }

        // Job Sheet Tools (1NF - separate table instead of JSON)
        if (!Schema::hasTable('job_sheet_tools')) {
            Schema::create('job_sheet_tools', function (Blueprint $table) {
                $table->id();
                $table->foreignId('job_sheet_id')->constrained()->onDelete('cascade');
                $table->string('tool_name');
                $table->string('quantity')->nullable();
                $table->integer('order')->default(0);
                $table->timestamps();

                $table->index(['job_sheet_id', 'order']);
            });
        }

        // Job Sheet Safety Requirements (1NF - separate table instead of JSON)
        if (!Schema::hasTable('job_sheet_safety_requirements')) {
            Schema::create('job_sheet_safety_requirements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('job_sheet_id')->constrained()->onDelete('cascade');
                $table->text('requirement');
                $table->integer('order')->default(0);
                $table->timestamps();

                $table->index(['job_sheet_id', 'order']);
            });
        }

        // Job Sheet Reference Materials (1NF - separate table instead of JSON)
        if (!Schema::hasTable('job_sheet_references')) {
            Schema::create('job_sheet_references', function (Blueprint $table) {
                $table->id();
                $table->foreignId('job_sheet_id')->constrained()->onDelete('cascade');
                $table->string('reference_title');
                $table->string('reference_type')->nullable();
                $table->integer('order')->default(0);
                $table->timestamps();

                $table->index(['job_sheet_id', 'order']);
            });
        }

        // Job Sheet Steps
        if (!Schema::hasTable('job_sheet_steps')) {
            Schema::create('job_sheet_steps', function (Blueprint $table) {
                $table->id();
                $table->foreignId('job_sheet_id')->constrained()->onDelete('cascade');
                $table->integer('step_number');
                $table->text('instruction');
                $table->text('expected_outcome');
                $table->string('image_path')->nullable();
                $table->timestamps();

                $table->index(['job_sheet_id', 'step_number']);
            });
        }

        // Job Sheet Step Warnings (1NF - separate table instead of JSON)
        if (!Schema::hasTable('job_sheet_step_warnings')) {
            Schema::create('job_sheet_step_warnings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('step_id')->constrained('job_sheet_steps')->onDelete('cascade');
                $table->text('warning');
                $table->integer('order')->default(0);
                $table->timestamps();

                $table->index(['step_id', 'order']);
            });
        }

        // Job Sheet Step Tips (1NF - separate table instead of JSON)
        if (!Schema::hasTable('job_sheet_step_tips')) {
            Schema::create('job_sheet_step_tips', function (Blueprint $table) {
                $table->id();
                $table->foreignId('step_id')->constrained('job_sheet_steps')->onDelete('cascade');
                $table->text('tip');
                $table->integer('order')->default(0);
                $table->timestamps();

                $table->index(['step_id', 'order']);
            });
        }

        // Job Sheet Submissions
        if (!Schema::hasTable('job_sheet_submissions')) {
            Schema::create('job_sheet_submissions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('job_sheet_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->text('observations');
                $table->text('challenges')->nullable();
                $table->text('solutions')->nullable();
                $table->integer('time_taken')->nullable()->comment('Time taken in minutes');
                $table->timestamp('submitted_at');
                $table->text('evaluator_notes')->nullable();
                $table->foreignId('evaluated_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('evaluated_at')->nullable();
                $table->timestamps();

                $table->index(['job_sheet_id', 'user_id']);
                $table->index(['user_id', 'submitted_at']);
            });
        }

        // Job Sheet Submission Completed Steps (1NF - separate table instead of JSON)
        if (!Schema::hasTable('job_sheet_submission_steps')) {
            Schema::create('job_sheet_submission_steps', function (Blueprint $table) {
                $table->id();
                $table->foreignId('submission_id')->constrained('job_sheet_submissions')->onDelete('cascade');
                $table->foreignId('step_id')->constrained('job_sheet_steps')->onDelete('cascade');
                $table->boolean('completed')->default(false);
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['submission_id', 'step_id']);
            });
        }

        // Homeworks
        if (!Schema::hasTable('homeworks')) {
            Schema::create('homeworks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('information_sheet_id')->constrained()->onDelete('cascade');
                $table->string('homework_number');
                $table->string('title');
                $table->text('description')->nullable();
                $table->text('instructions');
                $table->timestamp('due_date');
                $table->integer('max_points')->default(100);
                $table->boolean('allow_late_submission')->default(false);
                $table->integer('late_penalty')->default(0)->comment('Penalty percentage per day late');
                $table->timestamps();

                $table->index(['information_sheet_id', 'homework_number']);
                $table->index('due_date');
            });
        }

        // Homework Requirements (1NF - separate table instead of JSON)
        if (!Schema::hasTable('homework_requirements')) {
            Schema::create('homework_requirements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('homework_id')->constrained('homeworks')->onDelete('cascade');
                $table->text('requirement');
                $table->integer('order')->default(0);
                $table->timestamps();

                $table->index(['homework_id', 'order']);
            });
        }

        // Homework Submission Guidelines (1NF - separate table instead of JSON)
        if (!Schema::hasTable('homework_guidelines')) {
            Schema::create('homework_guidelines', function (Blueprint $table) {
                $table->id();
                $table->foreignId('homework_id')->constrained('homeworks')->onDelete('cascade');
                $table->text('guideline');
                $table->integer('order')->default(0);
                $table->timestamps();

                $table->index(['homework_id', 'order']);
            });
        }

        // Homework Reference Images (1NF - separate table instead of JSON)
        if (!Schema::hasTable('homework_reference_images')) {
            Schema::create('homework_reference_images', function (Blueprint $table) {
                $table->id();
                $table->foreignId('homework_id')->constrained('homeworks')->onDelete('cascade');
                $table->string('image_path');
                $table->string('caption')->nullable();
                $table->integer('order')->default(0);
                $table->timestamps();

                $table->index(['homework_id', 'order']);
            });
        }

        // Homework Submissions (3NF fix - remove max_points as it's redundant)
        if (!Schema::hasTable('homework_submissions')) {
            Schema::create('homework_submissions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('homework_id')->constrained('homeworks')->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('file_path');
                $table->text('description')->nullable();
                $table->decimal('work_hours', 4, 2)->nullable()->comment('Hours spent on the homework');
                $table->timestamp('submitted_at');
                $table->integer('score')->nullable();
                $table->text('evaluator_notes')->nullable();
                $table->foreignId('evaluated_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('evaluated_at')->nullable();
                $table->boolean('is_late')->default(false);
                $table->timestamps();

                $table->index(['homework_id', 'user_id']);
                $table->index(['user_id', 'submitted_at']);
            });
        }

        // Checklists
        if (!Schema::hasTable('checklists')) {
            Schema::create('checklists', function (Blueprint $table) {
                $table->id();
                $table->foreignId('information_sheet_id')->constrained()->onDelete('cascade');
                $table->string('checklist_number');
                $table->string('title');
                $table->text('description')->nullable();
                $table->foreignId('completed_by')->constrained('users')->onDelete('cascade');
                $table->timestamp('completed_at');
                $table->foreignId('evaluated_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('evaluated_at')->nullable();
                $table->text('evaluator_notes')->nullable();
                $table->timestamps();

                $table->index(['information_sheet_id', 'checklist_number']);
                $table->index(['completed_by', 'completed_at']);
            });
        }

        // Checklist Items (1NF - separate table instead of JSON)
        if (!Schema::hasTable('checklist_items')) {
            Schema::create('checklist_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('checklist_id')->constrained()->onDelete('cascade');
                $table->text('item');
                $table->integer('max_rating')->default(5);
                $table->integer('order')->default(0);
                $table->timestamps();

                $table->index(['checklist_id', 'order']);
            });
        }

        // Checklist Item Ratings (1NF - separate table for user ratings)
        if (!Schema::hasTable('checklist_item_ratings')) {
            Schema::create('checklist_item_ratings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('checklist_item_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->integer('rating');
                $table->text('remarks')->nullable();
                $table->timestamps();

                $table->index(['checklist_item_id', 'user_id']);
            });
        }

        // Performance Criteria for Task Sheets (4NF fix - remove polymorphic relationship)
        if (!Schema::hasTable('task_sheet_performance_criteria')) {
            Schema::create('task_sheet_performance_criteria', function (Blueprint $table) {
                $table->id();
                $table->foreignId('task_sheet_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->text('criteria');
                $table->decimal('score', 5, 2)->default(0);
                $table->text('evaluator_notes')->nullable();
                $table->foreignId('evaluated_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('completed_at');
                $table->timestamps();

                $table->index(['task_sheet_id', 'user_id']);
                $table->index(['user_id', 'completed_at']);
            });
        }

        // Performance Criteria for Job Sheets (4NF fix - separate table instead of polymorphic)
        if (!Schema::hasTable('job_sheet_performance_criteria')) {
            Schema::create('job_sheet_performance_criteria', function (Blueprint $table) {
                $table->id();
                $table->foreignId('job_sheet_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->text('criteria');
                $table->decimal('score', 5, 2)->default(0);
                $table->text('evaluator_notes')->nullable();
                $table->foreignId('evaluated_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('completed_at');
                $table->timestamps();

                $table->index(['job_sheet_id', 'user_id']);
                $table->index(['user_id', 'completed_at']);
            });
        }
    }

    public function down(): void
    {
        // Drop tables in reverse order to handle foreign key constraints
        Schema::dropIfExists('job_sheet_performance_criteria');
        Schema::dropIfExists('task_sheet_performance_criteria');
        Schema::dropIfExists('checklist_item_ratings');
        Schema::dropIfExists('checklist_items');
        Schema::dropIfExists('checklists');
        Schema::dropIfExists('homework_submissions');
        Schema::dropIfExists('homework_reference_images');
        Schema::dropIfExists('homework_guidelines');
        Schema::dropIfExists('homework_requirements');
        Schema::dropIfExists('homeworks');
        Schema::dropIfExists('job_sheet_submission_steps');
        Schema::dropIfExists('job_sheet_submissions');
        Schema::dropIfExists('job_sheet_step_tips');
        Schema::dropIfExists('job_sheet_step_warnings');
        Schema::dropIfExists('job_sheet_steps');
        Schema::dropIfExists('job_sheet_references');
        Schema::dropIfExists('job_sheet_safety_requirements');
        Schema::dropIfExists('job_sheet_tools');
        Schema::dropIfExists('job_sheet_objectives');
        Schema::dropIfExists('job_sheets');
        Schema::dropIfExists('task_sheet_submission_findings');
        Schema::dropIfExists('task_sheet_submissions');
        Schema::dropIfExists('task_sheet_items');
        Schema::dropIfExists('task_sheet_safety_precautions');
        Schema::dropIfExists('task_sheet_materials');
        Schema::dropIfExists('task_sheet_objectives');
        Schema::dropIfExists('task_sheets');
        Schema::dropIfExists('self_check_submission_answers');
        Schema::dropIfExists('self_check_submissions');
        Schema::dropIfExists('self_check_question_options');
        Schema::dropIfExists('self_check_questions');
        Schema::dropIfExists('self_checks');
        Schema::dropIfExists('information_sheets');
        Schema::dropIfExists('modules');
    }
};
