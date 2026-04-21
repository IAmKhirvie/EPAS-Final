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
        Schema::create('competency_test_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competency_test_id')->constrained()->onDelete('cascade');
            $table->text('question_text');
            $table->string('question_type')->default('multiple_choice');
            $table->unsignedInteger('points')->default(1);
            $table->json('options')->nullable();
            $table->text('correct_answer')->nullable();
            $table->text('explanation')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->unsignedInteger('part_index')->nullable(); // For grouping into parts
            $table->timestamps();

            $table->index(['competency_test_id', 'part_index']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competency_test_questions');
    }
};
