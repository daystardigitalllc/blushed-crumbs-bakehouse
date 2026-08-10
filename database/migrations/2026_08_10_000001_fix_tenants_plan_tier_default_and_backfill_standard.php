<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The original tenants migration defaulted plan_tier to 'standard', a third
 * tier that was never actually built — no controller, job, or webhook in the
 * app ever assigns it, only 'free'/'pro'/'canceled' are real. It survived as
 * a column default (reachable only by a raw insert with no explicit
 * plan_tier, which no app code path does) plus stray seeder/test fixtures.
 * Every plan-gating check in the app already treats anything that isn't
 * 'pro' as unpaid, so a 'standard' tenant behaves identically to 'free' —
 * this just makes that the honest label instead of a phantom tier.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('tenants')->where('plan_tier', 'standard')->update(['plan_tier' => 'free']);

        Schema::table('tenants', function ($table) {
            $table->string('plan_tier')->default('free')->change();
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function ($table) {
            $table->string('plan_tier')->default('standard')->change();
        });
    }
};
