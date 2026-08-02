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
            // SQLite treats enums as loose text, so no alter schema is needed for local development/testing.
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
            return;
        }

        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('new', 'invoiced', 'in_progress', 'ready', 'completed', 'cancelled') DEFAULT 'new'");
    }
};
