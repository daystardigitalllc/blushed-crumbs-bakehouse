<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('custom_domain_status')->default('unverified')->after('custom_domain');
            $table->string('custom_domain_token')->nullable()->after('custom_domain_status');
            $table->timestamp('custom_domain_verified_at')->nullable()->after('custom_domain_token');
            $table->timestamp('custom_domain_last_checked_at')->nullable()->after('custom_domain_verified_at');
            $table->text('custom_domain_last_error')->nullable()->after('custom_domain_last_checked_at');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'custom_domain_status',
                'custom_domain_token',
                'custom_domain_verified_at',
                'custom_domain_last_checked_at',
                'custom_domain_last_error',
            ]);
        });
    }
};
