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
        if (Schema::hasTable('topics') && !Schema::hasColumn('topics', 'parts')) {
            Schema::table('topics', function (Blueprint $table) {
                $table->json('parts')->nullable()->after('content');
            });
        }

        if (Schema::hasTable('topics') && !Schema::hasColumn('topics', 'topic_number')) {
            Schema::table('topics', function (Blueprint $table) {
                $table->string('topic_number', 50)->nullable()->after('title');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('topics')) {
            Schema::table('topics', function (Blueprint $table) {
                if (Schema::hasColumn('topics', 'parts')) {
                    $table->dropColumn('parts');
                }
                if (Schema::hasColumn('topics', 'topic_number')) {
                    $table->dropColumn('topic_number');
                }
            });
        }
    }
};
