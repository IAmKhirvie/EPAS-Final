<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->longText('introduction')->nullable()->change();
            $table->longText('how_to_use_cblm')->nullable()->change();
            $table->longText('learning_outcomes')->nullable()->change();
            $table->longText('table_of_contents')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->text('introduction')->nullable()->change();
            $table->text('how_to_use_cblm')->nullable()->change();
            $table->text('learning_outcomes')->nullable()->change();
            $table->text('table_of_contents')->nullable()->change();
        });
    }
};
