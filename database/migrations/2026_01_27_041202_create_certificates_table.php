<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('certificates')) {
            Schema::create('certificates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('course_id')->constrained()->onDelete('cascade');
                $table->string('certificate_number')->unique();
                $table->string('title');
                $table->text('description')->nullable();
                $table->date('issue_date');
                $table->date('expiry_date')->nullable();
                $table->enum('status', ['issued', 'revoked', 'expired'])->default('issued');
                $table->json('metadata')->nullable();
                $table->string('pdf_path')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'course_id']);
                $table->index('certificate_number');
            });
        }

        if (!Schema::hasTable('certificate_templates')) {
            Schema::create('certificate_templates', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('html_template');
                $table->string('background_image')->nullable();
                $table->json('settings')->nullable();
                $table->boolean('is_default')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('certificate_templates');
        Schema::dropIfExists('certificates');
    }
};
