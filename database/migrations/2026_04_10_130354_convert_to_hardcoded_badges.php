<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_badges', function (Blueprint $table) {
            $table->dropForeign(['badge_id']);
            $table->renameColumn('badge_id', 'badge_key');
            $table->string('badge_key', 50)->change();
        });

        Schema::dropIfExists('badges');
    }

    public function down(): void
    {
        // Reversing would require recreating the badges table structure
    }
};
