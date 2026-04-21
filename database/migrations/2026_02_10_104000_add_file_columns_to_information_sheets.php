<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('information_sheets') && !Schema::hasColumn('information_sheets', 'file_path')) {
            Schema::table('information_sheets', function (Blueprint $table) {
                $table->string('file_path')->nullable()->after('content');
                $table->string('original_filename')->nullable()->after('file_path');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('information_sheets')) {
            Schema::table('information_sheets', function (Blueprint $table) {
                if (Schema::hasColumn('information_sheets', 'file_path')) {
                    $table->dropColumn(['file_path', 'original_filename']);
                }
            });
        }
    }
};
