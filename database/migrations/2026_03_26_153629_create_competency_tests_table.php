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
        Schema::create('competency_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('instructions')->nullable();
            $table->unsignedInteger('time_limit')->nullable(); // in minutes
            $table->timestamp('due_date')->nullable();
            $table->unsignedTinyInteger('passing_score')->default(70);
            $table->unsignedInteger('total_points')->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('max_attempts')->nullable();
            $table->boolean('reveal_answers')->default(true);
            $table->boolean('randomize_questions')->default(false);
            $table->boolean('randomize_options')->default(false);
            $table->json('parts')->nullable(); // For grouping questions into parts
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['module_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competency_tests');
    }
};
