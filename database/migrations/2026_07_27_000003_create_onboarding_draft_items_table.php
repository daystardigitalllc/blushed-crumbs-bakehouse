<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('onboarding_draft_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('draft_id')->constrained('onboarding_drafts')->onDelete('cascade');
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('source_file_id')->nullable()->constrained('onboarding_files')->nullOnDelete();
            $table->string('type'); // product, category, content_field, gallery_image
            $table->string('dedupe_key'); // e.g. slugged product name — lets parallel jobs upsert-collapse duplicates
            $table->json('payload_ai')->nullable(); // what the AI proposed, untouched
            $table->json('payload_final')->nullable(); // starts as a copy of payload_ai, diverges once the baker edits it
            $table->string('status')->default('pending'); // pending, approved, rejected, edited
            $table->decimal('confidence', 5, 4)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['draft_id', 'type', 'dedupe_key']);
            $table->index(['draft_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onboarding_draft_items');
    }
};
