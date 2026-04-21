<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('courses') || !Schema::hasTable('modules')) {
            return;
        }

        // First, create the EPAS course if it doesn't exist
        $course = DB::table('courses')->where('course_code', 'EPAS-NCII')->first();

        if (!$course) {
            $courseId = DB::table('courses')->insertGetId([
                'course_code' => 'EPAS-NCII',
                'course_name' => 'Electronic Products Assembly and Servicing NCII',
                'description' => 'This course covers the competencies required to assemble and service electronic products according to industry standards.',
                'sector' => 'Electronics',
                'is_active' => true,
                'order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $courseId = $course->id;
        }

        // Update existing modules to belong to this course
        DB::table('modules')->whereNull('course_id')->update(['course_id' => $courseId]);
    }

    public function down()
    {
        // This migration cannot be reversed safely
    }
};