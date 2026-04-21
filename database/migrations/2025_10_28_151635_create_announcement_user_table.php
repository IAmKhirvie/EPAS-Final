<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('announcement_user')) {
            Schema::create('announcement_user', function (Blueprint $table) {
                $table->id();
                $table->foreignId('announcement_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->timestamp('read_at')->useCurrent();
                $table->timestamps();

                $table->unique(['announcement_id', 'user_id']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('announcement_user');
    }
};