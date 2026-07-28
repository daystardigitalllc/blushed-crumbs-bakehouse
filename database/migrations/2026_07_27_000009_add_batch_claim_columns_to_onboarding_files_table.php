<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('onboarding_files', function (Blueprint $table) {
            // Set atomically (single UPDATE...LIMIT) by DispatchPendingExtractionsJob
            // when it claims a file into an ExtractBatchJob run.
            $table->string('batch_id')->nullable()->after('status');
            // When the claim happened — FinalizeExtractionJob's stuck sweep resets
            // anything still 'extracting' after onboarding.extraction_stuck_minutes.
            $table->timestamp('claimed_at')->nullable()->after('batch_id');

            $table->index(['draft_id', 'kind', 'status']); // the claim UPDATE's WHERE clause
            $table->index('batch_id');
            $table->index(['status', 'claimed_at']); // the stuck sweep's WHERE clause
        });
    }

    public function down(): void
    {
        Schema::table('onboarding_files', function (Blueprint $table) {
            $table->dropIndex(['draft_id', 'kind', 'status']);
            $table->dropIndex(['batch_id']);
            $table->dropIndex(['status', 'claimed_at']);
            $table->dropColumn(['batch_id', 'claimed_at']);
        });
    }
};
