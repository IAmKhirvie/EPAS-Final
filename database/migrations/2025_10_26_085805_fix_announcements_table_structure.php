<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // First check if deadline column exists, if not add it
        if (!Schema::hasColumn('announcements', 'deadline')) {
            Schema::table('announcements', function (Blueprint $table) {
                $table->timestamp('deadline')->nullable()->after('publish_at');
            });
        }

        // Then add target_roles column
        if (!Schema::hasColumn('announcements', 'target_roles')) {
            Schema::table('announcements', function (Blueprint $table) {
                $table->string('target_roles')->default('all')->after('deadline');
            });
        }

        // Create announcement_reads table if it doesn't exist
        if (!Schema::hasTable('announcement_reads')) {
            Schema::create('announcement_reads', function (Blueprint $table) {
                $table->id();
                $table->foreignId('announcement_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->timestamps();
                
                $table->unique(['announcement_id', 'user_id']);
            });
        }
    }

    public function down()
    {
        // Remove the columns we added
        if (Schema::hasTable('announcements')) {
            $cols = [];
            if (Schema::hasColumn('announcements', 'deadline')) {
                $cols[] = 'deadline';
            }
            if (Schema::hasColumn('announcements', 'target_roles')) {
                $cols[] = 'target_roles';
            }
            if (!empty($cols)) {
                Schema::table('announcements', function (Blueprint $table) use ($cols) {
                    $table->dropColumn($cols);
                });
            }
        }

        // Drop the reads table
        Schema::dropIfExists('announcement_reads');
    }
};