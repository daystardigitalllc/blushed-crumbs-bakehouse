<?php

namespace App\Models\Onboarding;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Deliberately does NOT use BelongsToTenant — that trait's global scope only
 * activates on web requests (see BelongsToTenant docblock), and this model is
 * touched from queue jobs just as often as from controllers. Every query
 * against this table must scope tenant_id explicitly.
 */
class OnboardingDraft extends Model
{
    use HasFactory;

    protected $table = 'onboarding_drafts';

    protected $fillable = [
        'tenant_id',
        'parent_draft_id',
        'version',
        'status',
        'basics',
        'proposed_content',
        'theme_id',
        'logo_path',
        'model_versions',
        'confidence_overall',
        'resume_token',
        'import_manifest',
        'last_activity_at',
        'extraction_started_at',
        'extraction_completed_at',
        'imported_at',
        'ready_notified_at',
        'reminder_sent_at',
    ];

    protected $casts = [
        'basics' => 'array',
        'proposed_content' => 'array',
        'model_versions' => 'array',
        'confidence_overall' => 'decimal:4',
        'import_manifest' => 'array',
        'last_activity_at' => 'datetime',
        'extraction_started_at' => 'datetime',
        'extraction_completed_at' => 'datetime',
        'imported_at' => 'datetime',
        'ready_notified_at' => 'datetime',
        'reminder_sent_at' => 'datetime',
    ];

    /**
     * Statuses that count as "incomplete" for retention/resume purposes —
     * anything not yet imported and not actively being imported. Shared by
     * onboarding:prune-drafts (48h TTL) and onboarding:send-resume-reminders
     * (36h "still unreviewed" check) so the two never drift apart.
     */
    public const INCOMPLETE_STATUSES = ['collecting', 'extracting', 'synthesizing', 'ready_for_review', 'failed'];

    /**
     * Generates a resume token if one doesn't already exist. Idempotent —
     * safe to call from a mail-sending path without worrying about handing
     * out a second token for the same draft.
     */
    public function ensureResumeToken(): string
    {
        if (!$this->resume_token) {
            $this->resume_token = \Illuminate\Support\Str::random(48);
            $this->save();
        }

        return $this->resume_token;
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function parentDraft()
    {
        return $this->belongsTo(self::class, 'parent_draft_id');
    }

    public function files()
    {
        return $this->hasMany(OnboardingFile::class, 'draft_id');
    }

    public function items()
    {
        return $this->hasMany(OnboardingDraftItem::class, 'draft_id');
    }

    public function events()
    {
        return $this->hasMany(OnboardingEvent::class, 'draft_id');
    }
}
