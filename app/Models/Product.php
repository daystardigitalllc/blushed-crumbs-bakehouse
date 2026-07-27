<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;

class Product extends Model
{
    use HasFactory;
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'slug',
        'price',
        'price_min',
        'price_max',
        'price_unit',
        'category',
        'is_active',
        'is_featured',
        'sort_order',
        'source',
        'ai_confidence',
        'onboarding_file_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'ai_confidence' => 'decimal:4',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function onboardingFile()
    {
        return $this->belongsTo(\App\Models\Onboarding\OnboardingFile::class, 'onboarding_file_id');
    }
}
