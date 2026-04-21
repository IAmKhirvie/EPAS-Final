<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add template selection to courses and certificate customization options.
     */
    public function up(): void
    {
        // Add template selection to courses
        Schema::table('courses', function (Blueprint $table) {
            $table->string('certificate_template', 50)->default('default')->after('is_active');
            $table->string('certificate_background')->nullable()->after('certificate_template');
            $table->json('certificate_config')->nullable()->after('certificate_background');
        });

        // Add template used to certificates for historical record
        Schema::table('certificates', function (Blueprint $table) {
            $table->string('template_used', 50)->default('default')->after('pdf_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['certificate_template', 'certificate_background', 'certificate_config']);
        });

        Schema::table('certificates', function (Blueprint $table) {
            $table->dropColumn('template_used');
        });
    }
};
