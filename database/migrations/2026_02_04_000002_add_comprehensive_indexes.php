<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Check if an index already exists on a table.
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $indexes = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);

        return count($indexes) > 0;
    }

    public function up(): void
    {
        // 1. instructor_sections: index(['user_id', 'section'])
        //    Note: unique(['user_id', 'section']) already exists, but adding a regular index
        //    is skipped since unique constraint already acts as an index.
        //    Skipped — already covered by unique(['user_id', 'section']).

        // 2. enrollment_requests: composite indexes for common queries
        //    Existing: index(['status', 'created_at']), index('instructor_id')
        if (Schema::hasTable('enrollment_requests')) {
            Schema::table('enrollment_requests', function (Blueprint $table) {
                // index(['instructor_id', 'status']) — instructor_id alone exists, but not composite
                $table->index(['instructor_id', 'status'], 'enroll_req_instr_status_idx');
                // index(['student_id', 'status'])
                $table->index(['student_id', 'status'], 'enroll_req_student_status_idx');
                // index(['status', 'created_at']) — already exists from create migration, skip
            });
        }

        // 3. registrations: composite indexes for filtered queries
        //    Existing: index('email'), index('status'), index('verification_token')
        if (Schema::hasTable('registrations')) {
            Schema::table('registrations', function (Blueprint $table) {
                $table->index(['status', 'created_at'], 'reg_status_created_idx');
                $table->index(['email', 'status'], 'reg_email_status_idx');
            });
        }

        // 4. certificates: additional composite indexes
        //    Existing: index(['user_id', 'course_id']), unique('certificate_number'), index('certificate_number')
        if (Schema::hasTable('certificates')) {
            Schema::table('certificates', function (Blueprint $table) {
                if (Schema::hasColumn('certificates', 'status')) {
                    $table->index(['user_id', 'status'], 'cert_user_status_idx');
                    $table->index(['course_id', 'status'], 'cert_course_status_idx');
                }
                // certificate_number already has unique + index, skip
            });
        }

        // 5. user_badges: index(['user_id', 'earned_at']) or ['user_id', 'created_at']
        if (Schema::hasTable('user_badges')) {
            Schema::table('user_badges', function (Blueprint $table) {
                if (Schema::hasColumn('user_badges', 'earned_at')) {
                    $table->index(['user_id', 'earned_at'], 'ubadge_user_earned_idx');
                } else {
                    $table->index(['user_id', 'created_at'], 'ubadge_user_created_idx');
                }
            });
        }

        // 6. user_points: index(['user_id', 'type'])
        //    Existing: index(['user_id', 'created_at'])
        if (Schema::hasTable('user_points')) {
            if (Schema::hasColumn('user_points', 'type')) {
                Schema::table('user_points', function (Blueprint $table) {
                    $table->index(['user_id', 'type'], 'upoints_user_type_idx');
                });
            }
        }

        // 7. media: index(['mediable_type', 'mediable_id'])
        //    Note: Laravel's morphs() already creates this index, so skip if exists.
        if (Schema::hasTable('media')) {
            if (Schema::hasColumn('media', 'mediable_type') && Schema::hasColumn('media', 'mediable_id')) {
                // morphs() creates 'media_mediable_type_mediable_id_index' automatically.
                // Only add if it does not already exist (it should exist from morphs).
                // We wrap in try-catch since the index likely already exists.
                try {
                    Schema::table('media', function (Blueprint $table) {
                        $table->index(['mediable_type', 'mediable_id'], 'media_morph_idx');
                    });
                } catch (\Exception $e) {
                    // Index likely already exists from morphs(), safe to ignore.
                }
            }
        }

        // 8. settings: index(['user_id', 'key'])
        //    Existing: unique(['user_id', 'key']) — already acts as an index, skip.
        //    Skipped — already covered by unique(['user_id', 'key']).

        // 9. users: index(['role', 'stat']), index(['department_id', 'stat'])
        //    Existing: users_role_stat_index from add_performance_indexes
        if (Schema::hasTable('users')) {
            if (Schema::hasColumn('users', 'department_id') && Schema::hasColumn('users', 'stat')) {
                Schema::table('users', function (Blueprint $table) {
                    // index(['role', 'stat']) — already exists as users_role_stat_index, skip
                    $table->index(['department_id', 'stat'], 'users_dept_stat_idx');
                });
            }
        }

        // 10. courses: index(['instructor_id', 'is_active'])
        if (Schema::hasTable('courses')) {
            if (Schema::hasColumn('courses', 'instructor_id')) {
                Schema::table('courses', function (Blueprint $table) {
                    $table->index(['instructor_id', 'is_active'], 'courses_instr_active_idx');
                });
            }
        }

        // 11. modules: index(['course_id', 'is_active'])
        //     Already exists as modules_course_active_index from add_performance_indexes, skip.

        // 12. announcements: index(['user_id', 'created_at'])
        //     Existing: announcements_created_at_index, announcements_pinned_created_index
        if (Schema::hasTable('announcements')) {
            if (Schema::hasColumn('announcements', 'user_id')) {
                Schema::table('announcements', function (Blueprint $table) {
                    $table->index(['user_id', 'created_at'], 'announce_user_created_idx');
                });
            }
        }

        // 13. homework_submissions: index(['evaluated_by', 'evaluated_at'])
        if (Schema::hasTable('homework_submissions')) {
            if (Schema::hasColumn('homework_submissions', 'evaluated_by') && Schema::hasColumn('homework_submissions', 'evaluated_at')) {
                Schema::table('homework_submissions', function (Blueprint $table) {
                    $table->index(['evaluated_by', 'evaluated_at'], 'hw_sub_eval_by_at_idx');
                });
            }
        }

        // 14. audit_logs: index(['created_at'])
        //     Existing: index(['user_id', 'created_at']), but not created_at alone
        if (Schema::hasTable('audit_logs')) {
            if (Schema::hasColumn('audit_logs', 'created_at')) {
                Schema::table('audit_logs', function (Blueprint $table) {
                    $table->index(['created_at'], 'audit_created_idx');
                });
            }
        }
    }

    public function down(): void
    {
        // 2. enrollment_requests
        if (Schema::hasTable('enrollment_requests')) {
            Schema::table('enrollment_requests', function (Blueprint $table) {
                $table->dropIndex('enroll_req_instr_status_idx');
                $table->dropIndex('enroll_req_student_status_idx');
            });
        }

        // 3. registrations
        if (Schema::hasTable('registrations')) {
            Schema::table('registrations', function (Blueprint $table) {
                $table->dropIndex('reg_status_created_idx');
                $table->dropIndex('reg_email_status_idx');
            });
        }

        // 4. certificates
        if (Schema::hasTable('certificates')) {
            Schema::table('certificates', function (Blueprint $table) {
                if (Schema::hasColumn('certificates', 'status')) {
                    $table->dropIndex('cert_user_status_idx');
                    $table->dropIndex('cert_course_status_idx');
                }
            });
        }

        // 5. user_badges
        if (Schema::hasTable('user_badges')) {
            Schema::table('user_badges', function (Blueprint $table) {
                if (Schema::hasColumn('user_badges', 'earned_at')) {
                    $table->dropIndex('ubadge_user_earned_idx');
                } else {
                    $table->dropIndex('ubadge_user_created_idx');
                }
            });
        }

        // 6. user_points
        if (Schema::hasTable('user_points')) {
            if (Schema::hasColumn('user_points', 'type')) {
                Schema::table('user_points', function (Blueprint $table) {
                    $table->dropIndex('upoints_user_type_idx');
                });
            }
        }

        // 7. media
        if (Schema::hasTable('media')) {
            try {
                Schema::table('media', function (Blueprint $table) {
                    $table->dropIndex('media_morph_idx');
                });
            } catch (\Exception $e) {
                // Index may not have been created if morphs() index already existed.
            }
        }

        // 9. users
        if (Schema::hasTable('users')) {
            if (Schema::hasColumn('users', 'department_id') && Schema::hasColumn('users', 'stat')) {
                Schema::table('users', function (Blueprint $table) {
                    $table->dropIndex('users_dept_stat_idx');
                });
            }
        }

        // 10. courses
        if (Schema::hasTable('courses')) {
            if (Schema::hasColumn('courses', 'instructor_id')) {
                Schema::table('courses', function (Blueprint $table) {
                    $table->dropIndex('courses_instr_active_idx');
                });
            }
        }

        // 12. announcements
        if (Schema::hasTable('announcements')) {
            if (Schema::hasColumn('announcements', 'user_id')) {
                Schema::table('announcements', function (Blueprint $table) {
                    $table->dropIndex('announce_user_created_idx');
                });
            }
        }

        // 13. homework_submissions
        if (Schema::hasTable('homework_submissions')) {
            if (Schema::hasColumn('homework_submissions', 'evaluated_by') && Schema::hasColumn('homework_submissions', 'evaluated_at')) {
                Schema::table('homework_submissions', function (Blueprint $table) {
                    $table->dropIndex('hw_sub_eval_by_at_idx');
                });
            }
        }

        // 14. audit_logs
        if (Schema::hasTable('audit_logs')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->dropIndex('audit_created_idx');
            });
        }
    }
};
