<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name');                         
            $table->string('slug')->unique();                
            $table->string('domain')->unique();              // "doughmain.pro"
            $table->string('logo_url')->nullable();
            $table->json('branding_settings')->nullable();   // colors, fonts, marketing copy
            $table->json('theme_settings')->nullable();      // available themes for this vertical
            $table->json('feature_flags')->nullable();       // toggle features per brand
            $table->json('ai_prompts')->nullable();          // AI generation prompts per vertical
            $table->json('onboarding_questions')->nullable();// onboarding wizard config
            $table->json('pricing_plans')->nullable();       // plan tiers & pricing
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brands');
    }
};
