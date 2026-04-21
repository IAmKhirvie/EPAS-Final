<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('task_sheets', function (Blueprint $table) {
            $table->longText('document_content')->nullable()->after('original_filename');
        });

        Schema::table('job_sheets', function (Blueprint $table) {
            $table->longText('document_content')->nullable()->after('original_filename');
        });

        Schema::table('topics', function (Blueprint $table) {
            $table->longText('document_content')->nullable()->after('original_filename');
        });
    }

    public function down(): void
    {
        Schema::table('task_sheets', function (Blueprint $table) {
            $table->dropColumn('document_content');
        });

        Schema::table('job_sheets', function (Blueprint $table) {
            $table->dropColumn('document_content');
        });

        Schema::table('topics', function (Blueprint $table) {
            $table->dropColumn('document_content');
        });
    }
};
