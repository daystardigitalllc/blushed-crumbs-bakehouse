<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('invoice_number')->unique();
            $table->string('client_name');
            $table->string('client_email');
            $table->decimal('total_amount', 10, 2);
            $table->decimal('deposit_amount', 10, 2);
            $table->enum('status', ['unpaid', 'deposit_paid', 'paid_in_full', 'cancelled'])->default('unpaid');
            $table->string('payment_method_used')->nullable(); // Venmo, CashApp, PayPal, Stripe, etc.
            $table->timestamp('due_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
