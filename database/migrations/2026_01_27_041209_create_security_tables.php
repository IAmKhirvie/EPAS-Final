<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Login attempts for rate limiting
        if (!Schema::hasTable('login_attempts')) {
            Schema::create('login_attempts', function (Blueprint $table) {
                $table->id();
                $table->string('email')->nullable();
                $table->string('ip_address', 45);
                $table->boolean('successful')->default(false);
                $table->text('user_agent')->nullable();
                $table->timestamp('attempted_at');

                $table->index(['ip_address', 'attempted_at']);
                $table->index(['email', 'attempted_at']);
            });
        }

        // Two-factor authentication
        if (!Schema::hasTable('user_two_factor')) {
            Schema::create('user_two_factor', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('secret')->nullable(); // Encrypted 2FA secret
                $table->json('backup_codes')->nullable(); // Encrypted backup codes
                $table->boolean('is_enabled')->default(false);
                $table->timestamp('enabled_at')->nullable();
                $table->timestamp('last_used_at')->nullable();
                $table->timestamps();

                $table->unique('user_id');
            });
        }

        // Add 2FA columns to users
        if (!Schema::hasColumn('users', 'two_factor_required')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('two_factor_required')->default(false)->after('role');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'two_factor_required')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('two_factor_required');
            });
        }
        Schema::dropIfExists('user_two_factor');
        Schema::dropIfExists('login_attempts');
    }
};
