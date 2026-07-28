<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('onboarding_drafts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('parent_draft_id')->nullable()->constrained('onboarding_drafts')->nullOnDelete();
            $table->unsignedInteger('version')->default(1);
            $table->string('status')->default('collecting'); // collecting, extracting, synthesizing, ready_for_review, importing, imported, failed
            $table->json('basics')->nullable(); // step 1 form data
            $table->json('proposed_content')->nullable(); // synthesized site_content + theme + categories
            $table->string('theme_id')->nullable();
            $table->json('model_versions')->nullable(); // which Gemini model/prompt versions produced this draft
            $table->decimal('confidence_overall', 5, 4)->nullable();
            $table->string('resume_token')->nullable()->unique();
            $table->json('import_manifest')->nullable(); // Phase 6 copy manifest, written before any byte is copied
            $table->timestamp('last_activity_at')->nullable(); // drives the 48h retention/resume clock
            $table->timestamp('extraction_started_at')->nullable();
            $table->timestamp('extraction_completed_at')->nullable();
            $table->timestamp('imported_at')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'version']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onboarding_drafts');
    }
};
