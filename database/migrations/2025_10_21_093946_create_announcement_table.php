<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Change from CreateAnnouncementsTable to CreateAnnouncementTable
class CreateAnnouncementTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('announcements')) {
            Schema::create('announcements', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('content');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->boolean('is_pinned')->default(false);
                $table->boolean('is_urgent')->default(false);
                $table->timestamp('publish_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('announcement_comments')) {
            Schema::create('announcement_comments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('announcement_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->text('comment');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('type');
                $table->string('title');
                $table->text('message');
                $table->json('data')->nullable();
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('announcement_comments');
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('notifications');
    }
}