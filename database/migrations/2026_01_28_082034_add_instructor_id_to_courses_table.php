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
        if (!Schema::hasColumn('courses', 'instructor_id')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->foreignId('instructor_id')->nullable()->after('is_active')->constrained('users')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('courses', 'instructor_id')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->dropForeign(['instructor_id']);
                $table->dropColumn('instructor_id');
            });
        }
    }
};
