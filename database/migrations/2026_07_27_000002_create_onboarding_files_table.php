<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('onboarding_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('draft_id')->constrained('onboarding_drafts')->onDelete('cascade');
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade'); // explicit — these models don't use BelongsToTenant
            $table->string('original_filename')->nullable();
            $table->string('kind')->nullable(); // image, pdf, doc, unsupported
            $table->string('path'); // storage/app/onboarding/{tenant}/{draft}/... — never public/uploads
            $table->string('mime_type')->nullable(); // sniffed, not trusted from the client
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('content_hash'); // sha256 of the original upload, for dedupe
            $table->decimal('quality_score', 5, 2)->nullable(); // local score: resolution, sharpness, orientation
            $table->boolean('is_hero_candidate')->default(false);
            $table->string('alt_text')->nullable();
            $table->json('ai_labels')->nullable();
            $table->json('ai_result')->nullable(); // full Gemini extraction result for this file
            $table->string('status')->default('pending'); // pending, extracting, extracted, failed, unsupported, duplicate
            $table->text('error_message')->nullable();
            $table->timestamp('extracted_at')->nullable();
            $table->timestamps();

            $table->unique(['draft_id', 'content_hash']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onboarding_files');
    }
};
