<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->text('description')->nullable()->after('name');
            $table->string('slug')->nullable()->after('description');
            $table->decimal('price_min', 10, 2)->nullable()->after('price'); // menus say "$45-60" or "from $8"
            $table->decimal('price_max', 10, 2)->nullable()->after('price_min');
            $table->string('price_unit')->nullable()->after('price_max'); // e.g. "per dozen", "starting at"
            $table->boolean('is_featured')->default(false)->after('is_active');
            $table->string('source')->default('manual')->after('is_featured'); // manual, ai_onboarding
            $table->decimal('ai_confidence', 5, 4)->nullable()->after('source');
            $table->foreignId('onboarding_file_id')->nullable()->after('ai_confidence')
                ->constrained('onboarding_files')->nullOnDelete();

            $table->unique(['tenant_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'slug']);
            $table->dropConstrainedForeignId('onboarding_file_id');
            $table->dropColumn([
                'description',
                'slug',
                'price_min',
                'price_max',
                'price_unit',
                'is_featured',
                'source',
                'ai_confidence',
            ]);
        });
    }
};
