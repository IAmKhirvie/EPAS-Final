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
        Schema::table('courses', function (Blueprint $table) {
            $table->date('start_date')->nullable()->after('thumbnail');
            $table->date('end_date')->nullable()->after('start_date');
            $table->string('schedule_days', 100)->nullable()->after('end_date'); // e.g., "Mon,Wed,Fri"
            $table->time('schedule_time_start')->nullable()->after('schedule_days');
            $table->time('schedule_time_end')->nullable()->after('schedule_time_start');
            $table->integer('duration_hours')->nullable()->after('schedule_time_end'); // Total course hours
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn([
                'start_date',
                'end_date',
                'schedule_days',
                'schedule_time_start',
                'schedule_time_end',
                'duration_hours',
            ]);
        });
    }
};
