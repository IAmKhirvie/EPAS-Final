<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('announcements')) {
            // Make sure target_roles has a default value if it doesn't
            if (Schema::hasColumn('announcements', 'target_roles')) {
                Schema::table('announcements', function (Blueprint $table) {
                    $table->string('target_roles')->default('all')->change();
                });
            }

            // Add deadline column if it doesn't exist
            if (!Schema::hasColumn('announcements', 'deadline')) {
                Schema::table('announcements', function (Blueprint $table) {
                    $table->timestamp('deadline')->nullable()->after('publish_at');
                });
            }
        }
    }

    public function down()
    {
        // Don't drop columns in rollback to avoid data loss
    }
};