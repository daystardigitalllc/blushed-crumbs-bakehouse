<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('onboarding_drafts', function (Blueprint $table) {
            // Private draft-storage path to the baker's uploaded logo, set
            // during the basics step via Livewire's own uploader (a single
            // file, so none of the bulk-uploader's 20-file/session-lock
            // concerns apply). Copied into public/uploads at import, same as
            // gallery photos — nothing public until approved.
            $table->string('logo_path')->nullable()->after('theme_id');
        });
    }

    public function down(): void
    {
        Schema::table('onboarding_drafts', function (Blueprint $table) {
            $table->dropColumn('logo_path');
        });
    }
};
