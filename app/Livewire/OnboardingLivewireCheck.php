<?php

namespace App\Livewire;

use Livewire\Component;

/**
 * Trivial counter — Phase 7 of the onboarding rebuild exists solely to
 * de-risk the Livewire dependency (this app has been bitten by a
 * vendor-missing 500 before) before Phase 8 builds the real wizard on top
 * of it. Nothing here is meant to survive past that; it just proves the
 * full stack — Blade layout component, Livewire, Alpine bundled with it —
 * actually works end-to-end in this app before committing to it for real UI.
 */
class OnboardingLivewireCheck extends Component
{
    public int $count = 0;

    public function increment(): void
    {
        $this->count++;
    }

    public function render()
    {
        return view('livewire.onboarding-livewire-check')
            ->layout('components.layouts.onboarding', ['title' => 'Livewire Check']);
    }
}
