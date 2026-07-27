<?php

namespace App\Models\Onboarding;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * No BelongsToTenant — see OnboardingDraft docblock. Scope tenant_id explicitly.
 */
class OnboardingDraftItem extends Model
{
    use HasFactory;

    protected $table = 'onboarding_draft_items';

    protected $fillable = [
        'draft_id',
        'tenant_id',
        'source_file_id',
        'type',
        'dedupe_key',
        'payload_ai',
        'payload_final',
        'status',
        'confidence',
        'sort_order',
    ];

    protected $casts = [
        'payload_ai' => 'array',
        'payload_final' => 'array',
        'confidence' => 'decimal:4',
        'sort_order' => 'integer',
    ];

    public function draft()
    {
        return $this->belongsTo(OnboardingDraft::class, 'draft_id');
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function sourceFile()
    {
        return $this->belongsTo(OnboardingFile::class, 'source_file_id');
    }
}
