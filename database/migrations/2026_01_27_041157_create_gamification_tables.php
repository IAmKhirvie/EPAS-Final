<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Badges table - defines available badges
        if (!Schema::hasTable('badges')) {
            Schema::create('badges', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description');
                $table->string('icon')->nullable(); // Badge icon path
                $table->string('color')->default('#6d9773'); // Badge color
                $table->enum('type', ['achievement', 'milestone', 'streak', 'special'])->default('achievement');
                $table->integer('points_required')->default(0);
                $table->json('criteria')->nullable(); // JSON criteria for earning
                $table->boolean('is_active')->default(true);
                $table->integer('order')->default(0);
                $table->timestamps();
            });
        }

        // User badges pivot table
        if (!Schema::hasTable('user_badges')) {
            Schema::create('user_badges', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('badge_id')->constrained()->onDelete('cascade');
                $table->timestamp('earned_at');
                $table->json('metadata')->nullable(); // Extra info about how it was earned
                $table->timestamps();

                $table->unique(['user_id', 'badge_id']);
            });
        }

        // User points transactions
        if (!Schema::hasTable('user_points')) {
            Schema::create('user_points', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->integer('points');
                $table->enum('type', ['earned', 'spent', 'bonus', 'penalty'])->default('earned');
                $table->string('reason');
                $table->nullableMorphs('pointable'); // Related model (topic, self_check, etc.)
                $table->timestamps();

                $table->index(['user_id', 'created_at']);
            });
        }

        // Add total_points to users table
        if (!Schema::hasColumn('users', 'total_points')) {
            Schema::table('users', function (Blueprint $table) {
                $table->integer('total_points')->default(0)->after('stat');
                $table->integer('current_streak')->default(0)->after('total_points');
                $table->date('last_activity_date')->nullable()->after('current_streak');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'total_points')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn(['total_points', 'current_streak', 'last_activity_date']);
            });
        }
        Schema::dropIfExists('user_points');
        Schema::dropIfExists('user_badges');
        Schema::dropIfExists('badges');
    }
};
