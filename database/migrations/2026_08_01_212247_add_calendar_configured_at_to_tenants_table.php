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
            // booking_settings gets auto-populated with hardcoded defaults the
            // first time the dashboard loads (see AdminController::dashboard()),
            // so its presence alone can't tell us whether a baker has actually
            // touched their calendar settings vs. just logged in once. This
            // timestamp is only ever set inside saveBookingSettings() itself.
            $table->timestamp('calendar_configured_at')->nullable()->after('booking_settings');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn('calendar_configured_at');
        });
    }
};
