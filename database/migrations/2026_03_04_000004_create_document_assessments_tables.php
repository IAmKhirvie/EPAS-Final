<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('document_assessments')) {
            Schema::create('document_assessments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('information_sheet_id')->constrained()->cascadeOnDelete();
                $table->foreignId('created_by')->constrained('users');
                $table->string('assessment_number');
                $table->string('title');
                $table->text('description')->nullable();
                $table->text('instructions');
                $table->longText('document_content')->nullable();
                $table->string('file_path')->nullable();
                $table->string('original_filename')->nullable();
                $table->string('file_type', 10)->nullable();
                $table->unsignedInteger('max_points')->default(100);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();

                $table->index(['information_sheet_id', 'is_active']);
                $table->index('created_by');
            });
        }

        if (!Schema::hasTable('document_assessment_submissions')) {
            Schema::create('document_assessment_submissions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('document_assessment_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->longText('answer_text');
                $table->unsignedInteger('score')->nullable();
                $table->text('feedback')->nullable();
                $table->foreignId('evaluated_by')->nullable()->constrained('users');
                $table->datetime('evaluated_at')->nullable();
                $table->datetime('submitted_at');
                $table->boolean('is_late')->default(false);
                $table->timestamps();
                $table->softDeletes();

                $table->unique(['document_assessment_id', 'user_id'], 'doc_assessment_user_unique');
                $table->index('evaluated_by');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('document_assessment_submissions');
        Schema::dropIfExists('document_assessments');
    }
};
