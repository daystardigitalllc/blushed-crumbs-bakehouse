<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('logo_path')->nullable()->after('theme_id');
            $table->json('gallery_images')->nullable()->after('logo_path');
            $table->string('instagram_url')->nullable()->after('gallery_images');
            $table->string('facebook_url')->nullable()->after('instagram_url');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['logo_path', 'gallery_images', 'instagram_url', 'facebook_url']);
        });
    }
};
