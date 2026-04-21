<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('course_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('color', 7)->default('#6d9773'); // Hex color
            $table->string('icon')->nullable(); // FontAwesome icon class
            $table->text('description')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed default categories with preset colors
        DB::table('course_categories')->insert([
            [
                'name' => 'Technology',
                'slug' => 'technology',
                'color' => '#10b981', // Green
                'icon' => 'fas fa-microchip',
                'description' => 'Technology and IT related courses',
                'order' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Electronics',
                'slug' => 'electronics',
                'color' => '#ffb902', // Yellow (Theme)
                'icon' => 'fas fa-bolt',
                'description' => 'Electronics and electrical courses',
                'order' => 2,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Manufacturing',
                'slug' => 'manufacturing',
                'color' => '#f59e0b', // Amber/Orange
                'icon' => 'fas fa-industry',
                'description' => 'Manufacturing and production courses',
                'order' => 3,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Business',
                'slug' => 'business',
                'color' => '#8b5cf6', // Purple
                'icon' => 'fas fa-briefcase',
                'description' => 'Business and management courses',
                'order' => 4,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Healthcare',
                'slug' => 'healthcare',
                'color' => '#ef4444', // Red
                'icon' => 'fas fa-heartbeat',
                'description' => 'Healthcare and medical courses',
                'order' => 5,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Agriculture',
                'slug' => 'agriculture',
                'color' => '#22c55e', // Lime Green
                'icon' => 'fas fa-leaf',
                'description' => 'Agriculture and farming courses',
                'order' => 6,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Automotive',
                'slug' => 'automotive',
                'color' => '#64748b', // Slate
                'icon' => 'fas fa-car',
                'description' => 'Automotive and mechanics courses',
                'order' => 7,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Hospitality',
                'slug' => 'hospitality',
                'color' => '#ec4899', // Pink
                'icon' => 'fas fa-utensils',
                'description' => 'Hospitality and tourism courses',
                'order' => 8,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Construction',
                'slug' => 'construction',
                'color' => '#d97706', // Orange
                'icon' => 'fas fa-hard-hat',
                'description' => 'Construction and building courses',
                'order' => 9,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'General',
                'slug' => 'general',
                'color' => '#6366f1', // Indigo
                'icon' => 'fas fa-graduation-cap',
                'description' => 'General education courses',
                'order' => 10,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_categories');
    }
};
