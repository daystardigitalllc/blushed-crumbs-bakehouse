<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->foreignId('brand_id')->nullable()->after('id');
            $table->string('subdomain')->nullable()->unique()->after('domain');
            $table->string('custom_domain')->nullable()->unique()->after('subdomain');
            $table->json('ai_generated_content')->nullable()->after('section_settings');
            $table->boolean('onboarding_completed')->default(false)->after('ai_generated_content');
            $table->integer('max_reviews_display')->default(3)->after('onboarding_completed');
        });

        // Add customer_id to orders for CRM linkage
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('customer_id')->nullable()->after('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['brand_id', 'subdomain', 'custom_domain', 'ai_generated_content', 'onboarding_completed', 'max_reviews_display']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('customer_id');
        });
    }
};
