<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;

class GalleryItem extends Model
{
    use HasFactory;
    use BelongsToTenant;

    protected $table = 'galleries';

    protected $fillable = [
        'tenant_id',
        'title',
        'category',
        'image_url',
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
        'onboarding_file_id',
    ];

    protected $casts = [
        'quality_score' => 'decimal:2',
        'ai_labels' => 'array',
        'sort_order' => 'integer',
        'is_hero' => 'boolean',
        'is_visible' => 'boolean',
        'width' => 'integer',
        'height' => 'integer',
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
