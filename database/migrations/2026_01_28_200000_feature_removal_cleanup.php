<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Cleanup migration to remove unused features and add new functionality
     */
    public function up(): void
    {
        // Disable foreign key checks temporarily
        Schema::disableForeignKeyConstraints();

        // Drop messaging tables
        Schema::dropIfExists('messages');
        Schema::dropIfExists('conversation_participants');
        Schema::dropIfExists('conversations');

        // Drop learning path tables
        Schema::dropIfExists('user_learning_paths');
        Schema::dropIfExists('learning_path_items');
        Schema::dropIfExists('learning_paths');
        Schema::dropIfExists('module_prerequisites');

        // Drop attendance tables
        Schema::dropIfExists('attendance_records');
        Schema::dropIfExists('attendance_sessions');

        // Drop rubric foreign keys first, then tables
        if (Schema::hasColumn('homeworks', 'rubric_id')) {
            Schema::table('homeworks', function (Blueprint $table) {
                $table->dropForeign(['rubric_id']);
                $table->dropColumn('rubric_id');
            });
        }

        if (Schema::hasColumn('task_sheets', 'rubric_id')) {
            Schema::table('task_sheets', function (Blueprint $table) {
                $table->dropForeign(['rubric_id']);
                $table->dropColumn('rubric_id');
            });
        }

        if (Schema::hasColumn('job_sheets', 'rubric_id')) {
            Schema::table('job_sheets', function (Blueprint $table) {
                $table->dropForeign(['rubric_id']);
                $table->dropColumn('rubric_id');
            });
        }

        Schema::dropIfExists('rubric_evaluations');
        Schema::dropIfExists('rubric_criteria');
        Schema::dropIfExists('rubrics');

        // Add announcement feature to forums
        if (Schema::hasTable('forum_threads') && !Schema::hasColumn('forum_threads', 'is_announcement')) {
            Schema::table('forum_threads', function (Blueprint $table) {
                $table->boolean('is_announcement')->default(false)->after('is_locked');
                $table->integer('announcement_priority')->default(0)->after('is_announcement');
                $table->timestamp('announcement_expires_at')->nullable()->after('announcement_priority');
            });
        }

        // Add certificate approval workflow columns
        if (Schema::hasTable('certificates')) {
            Schema::table('certificates', function (Blueprint $table) {
                if (!Schema::hasColumn('certificates', 'requested_by')) {
                    $table->foreignId('requested_by')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
                }
                if (!Schema::hasColumn('certificates', 'requested_at')) {
                    $table->timestamp('requested_at')->nullable()->after('requested_by');
                }
                if (!Schema::hasColumn('certificates', 'approved_by')) {
                    $table->foreignId('approved_by')->nullable()->after('requested_at')->constrained('users')->nullOnDelete();
                }
                if (!Schema::hasColumn('certificates', 'approved_at')) {
                    $table->timestamp('approved_at')->nullable()->after('approved_by');
                }
                if (!Schema::hasColumn('certificates', 'status')) {
                    $table->enum('status', ['pending', 'approved', 'rejected', 'issued'])->default('pending')->after('approved_at');
                }
                if (!Schema::hasColumn('certificates', 'rejection_reason')) {
                    $table->text('rejection_reason')->nullable()->after('status');
                }
            });
        }

        // Re-enable foreign key checks
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove certificate approval columns
        if (Schema::hasTable('certificates')) {
            Schema::table('certificates', function (Blueprint $table) {
                $columns = ['rejection_reason', 'status', 'approved_at', 'approved_by', 'requested_at', 'requested_by'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('certificates', $column)) {
                        if (in_array($column, ['requested_by', 'approved_by'])) {
                            $table->dropForeign([$column]);
                        }
                        $table->dropColumn($column);
                    }
                }
            });
        }

        // Remove announcement columns from forum_threads
        if (Schema::hasTable('forum_threads')) {
            Schema::table('forum_threads', function (Blueprint $table) {
                $columns = ['announcement_expires_at', 'announcement_priority', 'is_announcement'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('forum_threads', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        // Note: We don't recreate the dropped tables in down() as they would be empty
        // and the original migrations should be used if the features need to be restored
    }
};
