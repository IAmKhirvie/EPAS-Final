<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('self_check_questions', 'options')) {
            Schema::table('self_check_questions', function (Blueprint $table) {
                $table->text('options')->nullable()->after('points');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('self_check_questions', 'options')) {
            Schema::table('self_check_questions', function (Blueprint $table) {
                $table->dropColumn('options');
            });
        }
    }
};
