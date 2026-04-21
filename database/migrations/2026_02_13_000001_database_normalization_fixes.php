<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Database normalization fixes (1NF-4NF):
     * 1. departments.description — fix nullabale() typo, make actually nullable
     * 2. users.stat — change string to tinyInteger (proper boolean semantics)
     * 3. Drop redundant announcement read tables (3 tables tracking same thing, none used)
     * 4. certificates.status — unify enum to include all needed values
     * 5. information_sheets — add missing description and is_active columns
     */
    public function up(): void
    {
        // 1. Fix departments.description nullable (typo nullabale() in original migration)
        if (Schema::hasTable('departments') && Schema::hasColumn('departments', 'description')) {
            Schema::table('departments', function (Blueprint $table) {
                $table->text('description')->nullable()->change();
            });
        }

        // 2. Change users.stat from string to tinyInteger
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'stat')) {
            if (DB::getDriverName() === 'sqlite') {
                // SQLite doesn't support ALTER COLUMN, but the data is already 0/1 strings
                // For SQLite testing, this is a no-op since types are flexible
            } else {
                // MySQL: convert string '0'/'1' to tinyInteger 0/1
                DB::statement("ALTER TABLE users MODIFY stat TINYINT UNSIGNED NOT NULL DEFAULT 0");
            }
        }

        // 3. Drop redundant announcement read-tracking tables
        // announcement_reads (from fix_announcements_table_structure) — keep this one
        // announcement_user_reads (duplicate, created twice) — drop
        // announcement_user (third duplicate) — drop
        // None of these tables are referenced by any Eloquent model
        Schema::dropIfExists('announcement_user_reads');
        Schema::dropIfExists('announcement_user');

        // 4. Fix certificates.status enum to include all workflow states
        if (Schema::hasTable('certificates') && Schema::hasColumn('certificates', 'status')) {
            if (DB::getDriverName() !== 'sqlite') {
                DB::statement("ALTER TABLE certificates MODIFY status ENUM('pending','approved','rejected','issued','revoked','expired') NOT NULL DEFAULT 'pending'");
            }
        }

        // 5. Add missing description and is_active columns to information_sheets
        if (Schema::hasTable('information_sheets')) {
            if (!Schema::hasColumn('information_sheets', 'description')) {
                Schema::table('information_sheets', function (Blueprint $table) {
                    $table->text('description')->nullable()->after('title');
                });
            }
            if (!Schema::hasColumn('information_sheets', 'is_active')) {
                Schema::table('information_sheets', function (Blueprint $table) {
                    $table->boolean('is_active')->default(true)->after('order');
                });
            }
        }
    }

    public function down(): void
    {
        // 5. Remove added columns from information_sheets
        if (Schema::hasTable('information_sheets')) {
            $dropCols = [];
            if (Schema::hasColumn('information_sheets', 'description')) {
                $dropCols[] = 'description';
            }
            if (Schema::hasColumn('information_sheets', 'is_active')) {
                $dropCols[] = 'is_active';
            }
            if (!empty($dropCols)) {
                Schema::table('information_sheets', function (Blueprint $table) use ($dropCols) {
                    $table->dropColumn($dropCols);
                });
            }
        }

        // 4. Revert certificates.status enum (original values)
        if (Schema::hasTable('certificates') && Schema::hasColumn('certificates', 'status')) {
            if (DB::getDriverName() !== 'sqlite') {
                DB::statement("ALTER TABLE certificates MODIFY status ENUM('issued','revoked','expired') NOT NULL DEFAULT 'issued'");
            }
        }

        // 3. Recreate dropped announcement tables
        if (!Schema::hasTable('announcement_user_reads')) {
            Schema::create('announcement_user_reads', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('announcement_id')->constrained()->onDelete('cascade');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
                $table->unique(['user_id', 'announcement_id']);
            });
        }

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

        // 2. Revert users.stat to string
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'stat')) {
            if (DB::getDriverName() !== 'sqlite') {
                DB::statement("ALTER TABLE users MODIFY stat VARCHAR(255) NOT NULL DEFAULT '0'");
            }
        }

        // 1. departments.description — no revert needed (was always intended to be nullable)
    }
};
