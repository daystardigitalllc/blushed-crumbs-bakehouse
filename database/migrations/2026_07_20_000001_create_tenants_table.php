<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('domain')->nullable();
            $table->string('owner_name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('plan_tier')->default('standard'); // standard ($29), pro ($50)
            $table->json('payment_settings')->nullable(); // Venmo, CashApp, PayPal, Zelle, Stripe
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
