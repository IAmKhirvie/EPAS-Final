<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('user_progress')) {
            Schema::create('user_progress', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('module_id')->constrained()->onDelete('cascade');
                $table->morphs('progressable');
                $table->enum('status', ['not_started', 'in_progress', 'completed', 'passed', 'failed'])->default('not_started');
                $table->integer('score')->nullable();
                $table->integer('max_score')->nullable();
                $table->integer('time_spent')->default(0);
                $table->integer('attempts')->default(0);
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->json('answers')->nullable();
                $table->timestamps();

                $table->unique(['user_id', 'module_id', 'progressable_type', 'progressable_id'], 'user_progress_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_progress');
    }
};