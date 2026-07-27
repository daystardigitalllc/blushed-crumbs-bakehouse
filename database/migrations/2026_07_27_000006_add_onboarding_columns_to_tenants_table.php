<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('address_line1')->nullable()->after('phone');
            $table->string('address_line2')->nullable()->after('address_line1');
            $table->string('city')->nullable()->after('address_line2');
            $table->string('state')->nullable()->after('city');
            $table->string('postal_code')->nullable()->after('state');
            $table->string('country_code')->nullable()->after('postal_code');
            $table->string('business_type')->nullable()->after('country_code');
            $table->string('website_url')->nullable()->after('business_type');
            $table->string('timezone')->nullable()->after('website_url');

            // Default 'v1' keeps every existing tenant on the legacy wizard — Phase 10 flips the
            // default to 'v2' for new signups, this column just makes the flag possible.
            $table->string('onboarding_flow_version')->default('v1')->after('onboarding_completed');
            $table->timestamp('onboarding_started_at')->nullable()->after('onboarding_flow_version');
            $table->timestamp('onboarding_completed_at')->nullable()->after('onboarding_started_at');
            $table->foreignId('active_onboarding_draft_id')->nullable()->after('onboarding_completed_at')
                ->constrained('onboarding_drafts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropConstrainedForeignId('active_onboarding_draft_id');
            $table->dropColumn([
                'address_line1',
                'address_line2',
                'city',
                'state',
                'postal_code',
                'country_code',
                'business_type',
                'website_url',
                'timezone',
                'onboarding_flow_version',
                'onboarding_started_at',
                'onboarding_completed_at',
            ]);
        });
    }
};
