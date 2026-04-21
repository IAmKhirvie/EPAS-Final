<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('self_checks', function (Blueprint $table) {
            $table->unsignedInteger('max_attempts')->nullable()->after('is_active');
            $table->boolean('reveal_answers')->default(true)->after('max_attempts');
        });
    }

    public function down(): void
    {
        Schema::table('self_checks', function (Blueprint $table) {
            $table->dropColumn(['max_attempts', 'reveal_answers']);
        });
    }
};
