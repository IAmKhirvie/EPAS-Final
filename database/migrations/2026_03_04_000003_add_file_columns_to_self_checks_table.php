<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('self_checks') && !Schema::hasColumn('self_checks', 'file_path')) {
            Schema::table('self_checks', function (Blueprint $table) {
                $table->string('file_path')->nullable()->after('instructions');
                $table->string('original_filename')->nullable()->after('file_path');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('self_checks')) {
            Schema::table('self_checks', function (Blueprint $table) {
                $table->dropColumn(['file_path', 'original_filename']);
            });
        }
    }
};
