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
        if (!Schema::hasColumn('users', 'email_changed_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->timestamp('email_changed_at')->nullable()->after('email_verified_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('users', 'email_changed_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('email_changed_at');
            });
        }
    }
};
