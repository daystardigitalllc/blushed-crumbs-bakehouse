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
        abort_unless($draft->tenant_id === auth()->user()->tenant_id || auth()->user()->isSuperAdmin(), 403);

        $this->draftId = $draftId;
    }

    /**
     * File extraction reaching 100% is NOT the same as the draft being done —
     * synthesis (writing site copy, picking categories, choosing the hero
     * photo) is a separate step that runs after, driven by the draft's own
     * status rather than file counts. Without reading this, the progress bar
     * has no way to know the difference between "actually finished" and
     * "files done, still building the site" — which is exactly what made the
     * synthesizing step look like a frozen page.
     */
    #[Computed]
    public function draftStatus(): string
    {
        return OnboardingDraft::where('id', $this->draftId)->value('status') ?? 'collecting';
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

    /**
     * Files claimed into a batch move to 'extracting' as a group and stay
     * there for the whole Gemini round-trip (often 30-60s) before flipping
     * to a terminal status all at once — so percentComplete alone sits at 0%
     * the entire time and then jumps straight to 100%, which reads as a
     * frozen page. The view uses this to show real "something is happening"
     * feedback instead of a static bar during that gap.
     */
    #[Computed]
    public function extractingCount(): int
    {
        return $this->counts()['extracting'] ?? 0;
    }

    public function render()
    {
        return view('livewire.onboarding.progress-bar');
    }
}
