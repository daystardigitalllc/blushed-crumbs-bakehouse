<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            // Set whenever a baker picks a Pro-only theme before actually paying —
            // the theme they *would* get is stashed here instead of applied, so it
            // can be offered again as a one-click upsell once they do pay. Cleared
            // (and applied) by the Stripe webhook on a real completed payment.
            $table->string('pending_pro_theme_id')->nullable()->after('theme_id');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn('pending_pro_theme_id');
        });
    }
};
