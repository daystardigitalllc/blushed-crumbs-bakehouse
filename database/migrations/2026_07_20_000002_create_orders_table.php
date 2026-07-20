<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('order_number')->unique();
            $table->string('client_name');
            $table->string('client_email');
            $table->string('client_phone');
            $table->date('due_date');
            $table->string('time_slot')->default('8:30 AM');
            $table->string('fulfillment_type')->default('pickup'); // pickup or delivery
            $table->text('delivery_address')->nullable();
            $table->json('items'); // array of selected products
            $table->json('flavors')->nullable(); // selected flavors
            $table->json('frosting')->nullable();
            $table->json('fillings')->nullable();
            $table->text('special_notes')->nullable();
            $table->text('allergies')->nullable();
            $table->json('social_follows')->nullable();
            $table->json('inspiration_files')->nullable();
            $table->decimal('subtotal', 10, 2)->default(0.00);
            $table->decimal('discount_amount', 10, 2)->default(0.00);
            $table->decimal('total_price', 10, 2);
            $table->decimal('deposit_amount', 10, 2); // 50% non-refundable deposit
            $table->boolean('deposit_paid')->default(false);
            $table->enum('status', ['new', 'invoiced', 'in_progress', 'ready', 'completed', 'cancelled'])->default('new');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
