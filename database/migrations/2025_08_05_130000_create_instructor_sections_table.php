<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the instructor_sections pivot table for many-to-many relationship
 * between instructors and sections/classes.
 *
 * This allows a single instructor to be assigned to multiple classes.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('instructor_sections')) {
            Schema::create('instructor_sections', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('section');
                $table->boolean('is_primary')->default(false); // Primary/main advisory section
                $table->timestamps();

                // Ensure unique instructor-section combinations
                $table->unique(['user_id', 'section']);

                // Index for faster lookups
                $table->index('section');
            });

            // Migrate existing advisory_section data to the new table
            if (Schema::hasColumn('users', 'advisory_section')) {
                $instructors = \DB::table('users')
                    ->where('role', 'instructor')
                    ->whereNotNull('advisory_section')
                    ->get();

                foreach ($instructors as $instructor) {
                    \DB::table('instructor_sections')->insert([
                        'user_id' => $instructor->id,
                        'section' => $instructor->advisory_section,
                        'is_primary' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('instructor_sections');
    }
};
