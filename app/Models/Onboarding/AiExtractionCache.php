<?php

namespace App\Models\Onboarding;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * No BelongsToTenant — see OnboardingDraft docblock. Scope tenant_id explicitly.
 */
class AiExtractionCache extends Model
{
    use HasFactory;

    protected $table = 'ai_extraction_cache';

    protected $fillable = [
        'cache_key',
        'tenant_id',
        'content_hash',
        'model',
        'prompt_version',
        'task',
        'result',
    ];

    protected $casts = [
        'result' => 'array',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
