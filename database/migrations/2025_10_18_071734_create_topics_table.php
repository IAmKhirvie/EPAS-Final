<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('topics')) {
            Schema::create('topics', function (Blueprint $table) {
                $table->id();
                $table->foreignId('information_sheet_id')->constrained()->onDelete('cascade');
                $table->string('title');
                $table->text('content')->nullable();
                $table->integer('order')->default(0);
                $table->timestamps();

                $table->index(['information_sheet_id', 'order']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('topics');
    }
};