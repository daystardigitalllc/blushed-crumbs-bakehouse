<?php

namespace App\Livewire\Onboarding;

use App\Models\Onboarding\OnboardingDraft;
use App\Models\Onboarding\OnboardingFile;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Refreshes on a browser event dispatched by public/js/onboarding-uploader.js
 * after each upload completes — not a timer. Selects only the columns the
 * grid renders, never ai_result/ai_labels (those can be large JSON blobs
 * that don't belong in a poll/refresh payload).
 */
class FileGrid extends Component
{
    #[Locked]
    public int $draftId;

    public function mount(int $draftId): void
    {
        $draft = OnboardingDraft::findOrFail($draftId);
        abort_unless($draft->tenant_id === auth()->user()->tenant_id || auth()->user()->isSuperAdmin(), 403);

        $this->draftId = $draftId;
    }

    #[On('file-uploaded')]
    public function refresh(): void
    {
        // Intentionally empty — its only job is to be a listened-for event
        // so Livewire round-trips and re-renders with a fresh #[Computed] read.
    }

    #[Computed]
    public function files(): Collection
    {
        return OnboardingFile::where('draft_id', $this->draftId)
            ->latest('id')
            ->limit(60)
            ->get(['id', 'original_filename', 'kind', 'status', 'quality_score']);
    }

    public function render()
    {
        return view('livewire.onboarding.file-grid');
    }
}
