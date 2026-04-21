<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('announcements')) {
            return;
        }

        // Add target_roles if missing
        if (!Schema::hasColumn('announcements', 'target_roles')) {
            Schema::table('announcements', function (Blueprint $table) {
                $table->string('target_roles')->default('all')->after('is_urgent');
            });
        }

        // Add deadline if missing
        if (!Schema::hasColumn('announcements', 'deadline')) {
            Schema::table('announcements', function (Blueprint $table) {
                $table->timestamp('deadline')->nullable()->after('publish_at');
            });
        }
    }

    public function down()
    {
        // Safe rollback - only drop if we added them
        if (Schema::hasColumn('announcements', 'deadline')) {
            Schema::table('announcements', function (Blueprint $table) {
                $table->dropColumn('deadline');
            });
        }
    }
};