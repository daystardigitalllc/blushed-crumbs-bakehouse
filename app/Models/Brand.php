<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'domain',
        'logo_url',
        'branding_settings',
        'theme_settings',
        'feature_flags',
        'ai_prompts',
        'onboarding_questions',
        'pricing_plans',
        'is_active',
    ];

    protected $casts = [
        'branding_settings' => 'array',
        'theme_settings' => 'array',
        'feature_flags' => 'array',
        'ai_prompts' => 'array',
        'onboarding_questions' => 'array',
        'pricing_plans' => 'array',
        'is_active' => 'boolean',
    ];

    public function tenants()
    {
        return $this->hasMany(Tenant::class);
    }
}
