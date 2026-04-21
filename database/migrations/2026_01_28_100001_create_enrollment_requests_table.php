<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('enrollment_requests')) {
            Schema::create('enrollment_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('instructor_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('student_id')->nullable()->constrained('users')->onDelete('cascade');
                $table->string('student_name')->nullable(); // For requesting new/unassigned students
                $table->string('student_email')->nullable();
                $table->string('section');
                $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
                $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
                $table->text('notes')->nullable();
                $table->text('admin_notes')->nullable();
                $table->timestamp('processed_at')->nullable();
                $table->timestamps();

                $table->index(['status', 'created_at']);
                $table->index('instructor_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollment_requests');
    }
};
