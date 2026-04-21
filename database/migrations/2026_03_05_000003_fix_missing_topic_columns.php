<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('topics', 'topic_number')) {
            Schema::table('topics', function (Blueprint $table) {
                $table->string('topic_number', 50)->nullable()->after('title');
            });
        }

        if (!Schema::hasColumn('topics', 'parts')) {
            Schema::table('topics', function (Blueprint $table) {
                $table->json('parts')->nullable()->after('content');
            });
        }
    }

    public function down(): void
    {
        Schema::table('topics', function (Blueprint $table) {
            if (Schema::hasColumn('topics', 'topic_number')) {
                $table->dropColumn('topic_number');
            }
            if (Schema::hasColumn('topics', 'parts')) {
                $table->dropColumn('parts');
            }
        });
    }
};
