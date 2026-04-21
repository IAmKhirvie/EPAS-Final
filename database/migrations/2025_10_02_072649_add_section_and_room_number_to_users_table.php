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
        if (!Schema::hasColumn('users', 'section')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('section', 50)->nullable()->after('department_id');
                $table->string('room_number', 20)->nullable()->after('section');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('users', 'section')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn(['section', 'room_number']);
            });
        }
    }
};