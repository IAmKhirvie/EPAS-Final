<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        if (!Schema::hasTable('pending_activities')) {
            Schema::create('pending_activities', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('description')->nullable();
                $table->foreignId('user_id')->constrained();
                $table->foreignId('module_id')->nullable()->constrained();
                $table->foreignId('assigned_by')->constrained('users');
                $table->enum('status', ['pending', 'completed', 'overdue'])->default('pending');
                $table->timestamp('deadline')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pending_activities');
    }
};
