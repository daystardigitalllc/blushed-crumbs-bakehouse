<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('onboarding_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('draft_id')->constrained('onboarding_drafts')->onDelete('cascade');
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('type'); // e.g. file_uploaded, extraction_completed, draft_synthesized, import_completed
            $table->string('message')->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('created_at')->useCurrent(); // append-only — no updated_at, rows are never modified

            $table->index(['draft_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onboarding_events');
    }
};
