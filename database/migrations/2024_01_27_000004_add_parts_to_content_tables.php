<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds 'parts' JSON column to all content tables for structured content with images
     */
    public function up(): void
    {
        // Add parts to self_checks table
        if (Schema::hasTable('self_checks') && !Schema::hasColumn('self_checks', 'parts')) {
            Schema::table('self_checks', function (Blueprint $table) {
                $table->json('parts')->nullable();
            });
        }

        // Add parts to task_sheets table
        if (Schema::hasTable('task_sheets') && !Schema::hasColumn('task_sheets', 'parts')) {
            Schema::table('task_sheets', function (Blueprint $table) {
                $table->json('parts')->nullable();
            });
        }

        // Add parts to job_sheets table
        if (Schema::hasTable('job_sheets') && !Schema::hasColumn('job_sheets', 'parts')) {
            Schema::table('job_sheets', function (Blueprint $table) {
                $table->json('parts')->nullable();
            });
        }

        // Add parts to homeworks table
        if (Schema::hasTable('homeworks') && !Schema::hasColumn('homeworks', 'parts')) {
            Schema::table('homeworks', function (Blueprint $table) {
                $table->json('parts')->nullable();
            });
        }

        // Add parts to checklists table
        if (Schema::hasTable('checklists') && !Schema::hasColumn('checklists', 'parts')) {
            Schema::table('checklists', function (Blueprint $table) {
                $table->json('parts')->nullable();
            });
        }

        // Add parts to information_sheets table
        if (Schema::hasTable('information_sheets') && !Schema::hasColumn('information_sheets', 'parts')) {
            Schema::table('information_sheets', function (Blueprint $table) {
                $table->json('parts')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['self_checks', 'task_sheets', 'job_sheets', 'homeworks', 'checklists', 'information_sheets'];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'parts')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn('parts');
                });
            }
        }
    }
};
