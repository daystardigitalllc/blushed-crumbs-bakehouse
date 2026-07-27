<?php

namespace App\Models\Onboarding;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Append-only activity stream — rows are never updated, only inserted, so
 * there's no updated_at column. No BelongsToTenant — see OnboardingDraft
 * docblock. Scope tenant_id explicitly.
 */
class OnboardingEvent extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $table = 'onboarding_events';

    protected $fillable = [
        'draft_id',
        'tenant_id',
        'type',
        'message',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
        'created_at' => 'datetime',
    ];

    public function draft()
    {
        return $this->belongsTo(OnboardingDraft::class, 'draft_id');
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
