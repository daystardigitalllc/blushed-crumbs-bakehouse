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
            $table->json('gallery_categories')->nullable()->after('gallery_images');
        });

        // Backfill with the categories that were previously hardcoded, so
        // existing GalleryItem rows (category = "Cakes"/"Cupcakes"/etc.)
        // keep matching a real, editable category instead of going orphaned.
        \DB::table('tenants')->whereNull('gallery_categories')->update([
            'gallery_categories' => json_encode(['Cakes', 'Cupcakes', 'Treats', 'Weddings']),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn('gallery_categories');
        });
    }
};
