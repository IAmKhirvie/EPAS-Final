<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('module_title');
        });

        // Backfill existing rows with slugs unique per course
        $modules = DB::table('modules')->whereNull('deleted_at')->get();
        $usedSlugs = [];

        foreach ($modules as $module) {
            $baseSlug = Str::slug($module->module_title);
            $slug = $baseSlug;
            $counter = 1;
            $key = $module->course_id . ':' . $slug;

            while (isset($usedSlugs[$key])) {
                $slug = $baseSlug . '-' . $counter;
                $key = $module->course_id . ':' . $slug;
                $counter++;
            }

            $usedSlugs[$key] = true;

            DB::table('modules')
                ->where('id', $module->id)
                ->update(['slug' => $slug]);
        }

        // Add composite unique index
        Schema::table('modules', function (Blueprint $table) {
            $table->unique(['course_id', 'slug'], 'modules_course_id_slug_unique');
        });
    }

    public function down(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->dropUnique('modules_course_id_slug_unique');
            $table->dropColumn('slug');
        });
    }
};
