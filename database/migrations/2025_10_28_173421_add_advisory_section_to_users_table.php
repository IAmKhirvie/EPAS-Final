<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('users', 'advisory_section')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('advisory_section')->nullable()->after('section');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('users', 'advisory_section')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('advisory_section');
            });
        }
    }
};