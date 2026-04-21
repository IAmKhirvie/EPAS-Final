<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            // Add module_id for module-level certificates (nullable, course-level if null)
            $table->foreignId('module_id')->nullable()->after('course_id')->constrained()->onDelete('cascade');

            // Instructor approval
            $table->foreignId('instructor_approved_by')->nullable()->after('approved_at')->constrained('users')->onDelete('set null');
            $table->timestamp('instructor_approved_at')->nullable()->after('instructor_approved_by');

            // Admin approval (rename existing approved_by to admin_approved_by for clarity)
            $table->foreignId('admin_approved_by')->nullable()->after('instructor_approved_at')->constrained('users')->onDelete('set null');
            $table->timestamp('admin_approved_at')->nullable()->after('admin_approved_by');

            // Index for module certificates
            $table->index(['user_id', 'module_id']);
        });

        // Update status enum to include pending states
        DB::statement("ALTER TABLE certificates MODIFY COLUMN status ENUM('pending', 'pending_instructor', 'pending_admin', 'issued', 'revoked', 'expired', 'rejected') DEFAULT 'pending'");
    }

    public function down(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->dropForeign(['module_id']);
            $table->dropForeign(['instructor_approved_by']);
            $table->dropForeign(['admin_approved_by']);
            $table->dropIndex(['user_id', 'module_id']);
            $table->dropColumn(['module_id', 'instructor_approved_by', 'instructor_approved_at', 'admin_approved_by', 'admin_approved_at']);
        });

        DB::statement("ALTER TABLE certificates MODIFY COLUMN status ENUM('issued', 'revoked', 'expired') DEFAULT 'issued'");
    }
};
