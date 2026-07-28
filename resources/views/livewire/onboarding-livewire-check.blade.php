<div class="ob-card">
    <h1>Livewire dependency check</h1>
    <p class="ob-subtitle">
        Super-admin only. Proves Livewire + Alpine work end-to-end in this app before Phase 8 builds the real onboarding wizard on top of it.
    </p>

    <p style="font-size: 2.5rem; font-weight: 800; margin: 0 0 var(--ob-space-3); color: var(--ob-color-primary);">
        {{ $count }}
    </p>

    <button
        type="button"
        class="ob-btn ob-btn-primary"
        wire:click="increment"
        wire:loading.attr="disabled"
    >
        Increment
    </button>
</div>
