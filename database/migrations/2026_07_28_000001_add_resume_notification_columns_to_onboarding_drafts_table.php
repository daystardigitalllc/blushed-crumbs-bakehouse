<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('onboarding_drafts', function (Blueprint $table) {
            // Guards against sending either resume email more than once per draft.
            // ready_notified_at: "your site is ready to review" email, sent once
            // extraction finishes if the baker has already navigated away.
            // reminder_sent_at: the single 36h-inactive "expires in 12 hours" email.
            $table->timestamp('ready_notified_at')->nullable()->after('imported_at');
            $table->timestamp('reminder_sent_at')->nullable()->after('ready_notified_at');
        });
    }

    public function down(): void
    {
        Schema::table('onboarding_drafts', function (Blueprint $table) {
            $table->dropColumn(['ready_notified_at', 'reminder_sent_at']);
        });
    }
};
