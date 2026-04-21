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
        if (Schema::hasTable('modules') && !Schema::hasColumn('modules', 'images')) {
            Schema::table('modules', function (Blueprint $table) {
                $table->json('images')->nullable()->after('learning_outcomes');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('modules') && Schema::hasColumn('modules', 'images')) {
            Schema::table('modules', function (Blueprint $table) {
                $table->dropColumn('images');
            });
        }
    }
};
