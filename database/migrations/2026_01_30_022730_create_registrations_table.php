<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This table stores pending student registrations.
     * Users are moved to 'users' table only when:
     * 1. Email is verified (email_verified_at is not null)
     * 2. Admin has approved (admin_approved_at is not null)
     */
    public function up(): void
    {
        if (!Schema::hasTable('registrations')) {
            Schema::create('registrations', function (Blueprint $table) {
                $table->id();
                $table->string('first_name');
                $table->string('middle_name')->nullable();
                $table->string('last_name');
                $table->string('ext_name')->nullable();
                $table->string('email')->unique();
                $table->string('password');

                // Verification & Approval Status
                $table->string('verification_token', 64)->nullable();
                $table->timestamp('verification_token_expires')->nullable();
                $table->timestamp('email_verified_at')->nullable();
                $table->timestamp('admin_approved_at')->nullable();
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->text('rejection_reason')->nullable();

                // Status: pending, email_verified, approved, rejected, transferred
                $table->string('status')->default('pending');

                $table->timestamps();

                $table->index('email');
                $table->index('status');
                $table->index('verification_token');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
