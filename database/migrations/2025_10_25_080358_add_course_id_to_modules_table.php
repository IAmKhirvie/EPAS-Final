<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('modules') && !Schema::hasColumn('modules', 'course_id')) {
            Schema::table('modules', function (Blueprint $table) {
                $table->foreignId('course_id')->after('id')->nullable()->constrained()->onDelete('cascade');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('modules') && Schema::hasColumn('modules', 'course_id')) {
            Schema::table('modules', function (Blueprint $table) {
                $table->dropForeign(['course_id']);
                $table->dropColumn('course_id');
            });
        }
    }
};