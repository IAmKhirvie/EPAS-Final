<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add due_date to self_checks (time_limit already exists)
        Schema::table('self_checks', function (Blueprint $table) {
            $table->dateTime('due_date')->nullable()->after('time_limit');
        });

        // Add time_limit and due_date to document_assessments
        Schema::table('document_assessments', function (Blueprint $table) {
            $table->integer('time_limit')->nullable()->after('max_points')->comment('Time limit in minutes');
            $table->dateTime('due_date')->nullable()->after('time_limit');
        });
    }

    public function down(): void
    {
        Schema::table('self_checks', function (Blueprint $table) {
            $table->dropColumn('due_date');
        });

        Schema::table('document_assessments', function (Blueprint $table) {
            $table->dropColumn(['time_limit', 'due_date']);
        });
    }
};
