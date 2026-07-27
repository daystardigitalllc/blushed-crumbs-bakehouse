<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('galleries', function (Blueprint $table) {
            $table->string('alt_text')->nullable()->after('image_url');
            $table->decimal('quality_score', 5, 2)->nullable()->after('alt_text');
            $table->text('caption')->nullable()->after('quality_score');
            $table->json('ai_labels')->nullable()->after('caption');
            $table->unsignedInteger('sort_order')->default(0)->after('ai_labels');
            $table->boolean('is_hero')->default(false)->after('sort_order');
            $table->boolean('is_visible')->default(true)->after('is_hero');
            $table->string('image_hash')->nullable()->after('is_visible');
            $table->unsignedInteger('width')->nullable()->after('image_hash');
            $table->unsignedInteger('height')->nullable()->after('width');
            $table->string('source')->default('manual')->after('height'); // manual, ai_onboarding
            $table->foreignId('onboarding_file_id')->nullable()->after('source')
                ->constrained('onboarding_files')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('galleries', function (Blueprint $table) {
            $table->dropConstrainedForeignId('onboarding_file_id');
            $table->dropColumn([
                'alt_text',
                'quality_score',
                'caption',
                'ai_labels',
                'sort_order',
                'is_hero',
                'is_visible',
                'image_hash',
                'width',
                'height',
                'source',
            ]);
        });
    }
};
