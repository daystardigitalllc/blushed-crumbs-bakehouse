<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            $this->rebuildSqliteOrdersTable(['new', 'invoiced', 'paid', 'in_progress', 'ready', 'completed', 'cancelled']);

            return;
        }

        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('new', 'invoiced', 'paid', 'in_progress', 'ready', 'completed', 'cancelled') DEFAULT 'new'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            $this->rebuildSqliteOrdersTable(['new', 'invoiced', 'in_progress', 'ready', 'completed', 'cancelled']);

            return;
        }

        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('new', 'invoiced', 'in_progress', 'ready', 'completed', 'cancelled') DEFAULT 'new'");
    }

    /**
     * SQLite emulates enum() columns via a CHECK constraint, which can't be altered
     * in place. Rebuild the table with the new constraint and copy the data over.
     */
    private function rebuildSqliteOrdersTable(array $allowedStatuses): void
    {
        DB::statement('PRAGMA foreign_keys=off');

        Schema::rename('orders', 'orders_old');

        // SQLite keeps the unique index under its original name after a table
        // rename, which would collide with the same index name on the new table.
        DB::statement('DROP INDEX IF EXISTS orders_order_number_unique');

        Schema::create('orders', function (Blueprint $table) use ($allowedStatuses) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('order_number')->unique();
            $table->string('client_name');
            $table->string('client_email');
            $table->string('client_phone');
            $table->date('due_date');
            $table->string('time_slot')->default('8:30 AM');
            $table->string('fulfillment_type')->default('pickup');
            $table->text('delivery_address')->nullable();
            $table->json('items');
            $table->json('flavors')->nullable();
            $table->json('frosting')->nullable();
            $table->json('fillings')->nullable();
            $table->text('special_notes')->nullable();
            $table->text('allergies')->nullable();
            $table->json('social_follows')->nullable();
            $table->json('inspiration_files')->nullable();
            $table->decimal('subtotal', 10, 2)->default(0.00);
            $table->decimal('discount_amount', 10, 2)->default(0.00);
            $table->decimal('total_price', 10, 2);
            $table->decimal('deposit_amount', 10, 2);
            $table->boolean('deposit_paid')->default(false);
            $table->enum('status', $allowedStatuses)->default('new');
            $table->timestamps();
        });

        $columns = implode(', ', [
            'id', 'tenant_id', 'order_number', 'client_name', 'client_email', 'client_phone',
            'due_date', 'time_slot', 'fulfillment_type', 'delivery_address', 'items', 'flavors',
            'frosting', 'fillings', 'special_notes', 'allergies', 'social_follows', 'inspiration_files',
            'subtotal', 'discount_amount', 'total_price', 'deposit_amount', 'deposit_paid', 'status',
            'created_at', 'updated_at',
        ]);

        DB::statement("INSERT INTO orders ({$columns}) SELECT {$columns} FROM orders_old");

        Schema::drop('orders_old');

        DB::statement('PRAGMA foreign_keys=on');
    }
};
