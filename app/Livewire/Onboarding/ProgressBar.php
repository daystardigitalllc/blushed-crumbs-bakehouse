<?php

namespace App\Livewire\Onboarding;

use App\Models\Onboarding\OnboardingDraft;
use App\Services\Onboarding\OnboardingProgress;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Own polling scope (wire:poll.visible.2s in the view) so it can refresh
 * independently of ActivityFeed/FileGrid — a tiny payload (a handful of
 * status counts) every 2s, not the whole page.
 */
class ProgressBar extends Component
{
    #[Locked]
    public int $draftId;

    public function mount(int $draftId): void
    {
        $draft = OnboardingDraft::findOrFail($draftId);
        abort_unless($draft->tenant_id === auth()->user()->tenant_id, 403);

        $this->draftId = $draftId;
    }

    #[Computed]
    public function counts(): array
    {
        return OnboardingProgress::statusCounts($this->draftId);
    }

    #[Computed]
    public function percentComplete(): int
    {
        $counts = $this->counts();
        $total = array_sum($counts);
        if ($total === 0) {
            return 0;
        }

        $done = array_sum(array_intersect_key($counts, array_flip(OnboardingProgress::TERMINAL_STATUSES)));

        return (int) round(($done / $total) * 100);
    }

    public function render()
    {
        return view('livewire.onboarding.progress-bar');
    }
}
