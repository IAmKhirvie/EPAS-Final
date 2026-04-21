<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('self_check_submissions', 'answers')) {
            Schema::table('self_check_submissions', function (Blueprint $table) {
                $table->json('answers')->nullable()->after('passed');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('self_check_submissions', 'answers')) {
            Schema::table('self_check_submissions', function (Blueprint $table) {
                $table->dropColumn('answers');
            });
        }
    }
};
