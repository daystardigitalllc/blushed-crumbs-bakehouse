<?php

namespace App\Models\Onboarding;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * No BelongsToTenant — see OnboardingDraft docblock. Scope tenant_id explicitly.
 */
class OnboardingFile extends Model
{
    use HasFactory;

    protected $table = 'onboarding_files';

    protected $fillable = [
        'draft_id',
        'tenant_id',
        'original_filename',
        'kind',
        'path',
        'mime_type',
        'width',
        'height',
        'file_size',
        'content_hash',
        'quality_score',
        'is_hero_candidate',
        'alt_text',
        'ai_labels',
        'ai_result',
        'status',
        'error_message',
        'extracted_at',
    ];

    protected $casts = [
        'width' => 'integer',
        'height' => 'integer',
        'file_size' => 'integer',
        'quality_score' => 'decimal:2',
        'is_hero_candidate' => 'boolean',
        'ai_labels' => 'array',
        'ai_result' => 'array',
        'extracted_at' => 'datetime',
    ];

    public function draft()
    {
        return $this->belongsTo(OnboardingDraft::class, 'draft_id');
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function draftItems()
    {
        return $this->hasMany(OnboardingDraftItem::class, 'source_file_id');
    }
}
