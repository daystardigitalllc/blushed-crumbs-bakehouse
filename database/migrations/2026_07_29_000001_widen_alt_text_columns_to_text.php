<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Real production incident: Gemini's alt_text descriptions regularly exceed
 * VARCHAR(255) (e.g. a 270-character description of a Mother's Day cake
 * photo), and MySQL's default strict mode turns that into a hard
 * INSERT/UPDATE failure rather than a silent truncation — which killed the
 * whole extraction batch job, not just that one field. Raw SQL rather than
 * Schema::table()->change() to avoid adding doctrine/dbal as a dependency
 * for a two-column type change.
 */
return new class extends Migration
{
    public function up(): void
    {
        // SQLite (used by the test suite) doesn't enforce VARCHAR length at
        // all -- TEXT and VARCHAR(255) are identical there, so there's
        // nothing to fix and the MySQL-specific MODIFY syntax would just
        // fail on it.
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE onboarding_files MODIFY alt_text TEXT NULL');
        DB::statement('ALTER TABLE galleries MODIFY alt_text TEXT NULL');
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE onboarding_files MODIFY alt_text VARCHAR(255) NULL');
        DB::statement('ALTER TABLE galleries MODIFY alt_text VARCHAR(255) NULL');
    }
};
