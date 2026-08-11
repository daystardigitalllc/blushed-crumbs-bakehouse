<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            // Keyed by section id (hero, about, highlights, ...), each value an
            // associative array of whichever of {bg, heading, text, button_bg,
            // button_text} the baker has actually overridden for that section.
            // Replaces the flat primary_color/secondary_color/button_color/
            // text_color columns -- those stay in place (still read by the
            // storefront color-override partial as a tenant-wide fallback) but
            // are no longer exposed in the dashboard now that colors are
            // chosen per-section instead.
            $table->json('section_colors')->nullable()->after('text_color');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn('section_colors');
        });
    }
};
