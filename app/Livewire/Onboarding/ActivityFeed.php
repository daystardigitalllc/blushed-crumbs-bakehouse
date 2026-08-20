<?php

namespace App\Livewire\Onboarding;

use App\Models\Onboarding\OnboardingDraft;
use App\Models\Onboarding\OnboardingEvent;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Its own polling scope, separate from ProgressBar/FileGrid. Selects only
 * the columns the view renders (id, type, message, created_at) — never the
 * JSON payload column — so this stays a tiny poll payload no matter how
 * much detail an event's payload carries.
 */
class ActivityFeed extends Component
{
    #[Locked]
    public int $draftId;

    public function mount(int $draftId): void
    {
        $draft = OnboardingDraft::findOrFail($draftId);
        abort_unless($draft->tenant_id === auth()->user()->tenant_id || auth()->user()->isSuperAdmin(), 403);

        $this->draftId = $draftId;
    }

    #[Computed]
    public function events(): Collection
    {
        return OnboardingEvent::where('draft_id', $this->draftId)
            ->latest('id')
            ->limit(20)
            ->get(['id', 'type', 'message', 'created_at']);
    }

    public function render()
    {
        return view('livewire.onboarding.activity-feed');
    }
}
