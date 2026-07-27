<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_extraction_cache', function (Blueprint $table) {
            $table->id();
            $table->string('cache_key')->unique(); // sha256(tenant|content_hash|model|prompt_version|task)
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('content_hash');
            $table->string('model');
            $table->string('prompt_version');
            $table->string('task'); // e.g. extract_products, generate_alt_text
            $table->json('result');
            $table->timestamps();

            $table->index(['tenant_id', 'content_hash']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_extraction_cache');
    }
};
