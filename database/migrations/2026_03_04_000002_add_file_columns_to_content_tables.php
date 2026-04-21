<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('topics') && !Schema::hasColumn('topics', 'file_path')) {
            Schema::table('topics', function (Blueprint $table) {
                $table->string('file_path')->nullable()->after('content');
                $table->string('original_filename')->nullable()->after('file_path');
            });
        }

        if (Schema::hasTable('job_sheets') && !Schema::hasColumn('job_sheets', 'file_path')) {
            Schema::table('job_sheets', function (Blueprint $table) {
                $table->string('file_path')->nullable()->after('description');
                $table->string('original_filename')->nullable()->after('file_path');
            });
        }

        if (Schema::hasTable('task_sheets') && !Schema::hasColumn('task_sheets', 'file_path')) {
            Schema::table('task_sheets', function (Blueprint $table) {
                $table->string('file_path')->nullable()->after('image_path');
                $table->string('original_filename')->nullable()->after('file_path');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('topics', 'file_path')) {
            Schema::table('topics', function (Blueprint $table) {
                $table->dropColumn(['file_path', 'original_filename']);
            });
        }

        if (Schema::hasColumn('job_sheets', 'file_path')) {
            Schema::table('job_sheets', function (Blueprint $table) {
                $table->dropColumn(['file_path', 'original_filename']);
            });
        }

        if (Schema::hasColumn('task_sheets', 'file_path')) {
            Schema::table('task_sheets', function (Blueprint $table) {
                $table->dropColumn(['file_path', 'original_filename']);
            });
        }
    }
};
