<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->text('shipping_address')->nullable()->after('delivery_address');
            $table->string('shipping_state')->nullable()->after('shipping_address');
            $table->decimal('shipping_fee', 10, 2)->nullable()->default(0)->after('shipping_state');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['shipping_address', 'shipping_state', 'shipping_fee']);
        });
    }
};
