<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('primary_color', 7)->nullable()->after('theme_id');
            $table->string('secondary_color', 7)->nullable()->after('primary_color');
            $table->string('button_color', 7)->nullable()->after('secondary_color');
            $table->string('text_color', 7)->nullable()->after('button_color');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['primary_color', 'secondary_color', 'button_color', 'text_color']);
        });
    }
};
