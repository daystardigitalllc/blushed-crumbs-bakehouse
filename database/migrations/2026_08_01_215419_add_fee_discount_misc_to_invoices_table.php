<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // The pre-adjustment base amount — kept separate from total_amount
            // so fee/discount/misc can be recalculated without losing the
            // original order total they were applied on top of.
            $table->decimal('subtotal', 10, 2)->nullable()->after('order_id');
            $table->decimal('fee_amount', 10, 2)->default(0)->after('total_amount');
            $table->string('fee_label')->nullable()->after('fee_amount');
            $table->decimal('discount_amount', 10, 2)->default(0)->after('fee_label');
            $table->string('discount_label')->nullable()->after('discount_amount');
            $table->decimal('misc_amount', 10, 2)->default(0)->after('discount_label');
            $table->string('misc_label')->nullable()->after('misc_amount');
        });

        // Backfill existing invoices: their current total_amount IS the
        // subtotal (no adjustments existed before this migration), so this
        // keeps every pre-existing invoice's displayed total unchanged.
        DB::table('invoices')->whereNull('subtotal')->update([
            'subtotal' => DB::raw('total_amount'),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'subtotal',
                'fee_amount',
                'fee_label',
                'discount_amount',
                'discount_label',
                'misc_amount',
                'misc_label',
            ]);
        });
    }
};
