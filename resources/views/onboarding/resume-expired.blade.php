<x-layouts.onboarding title="Draft expired">
    <div class="ob-card" style="text-align: center;">
        <h1>This link has expired</h1>
        <p class="ob-subtitle">
            This onboarding draft is no longer available — it was either already built into a live
            site, or it expired from inactivity and was cleared out.
        </p>
        <a href="{{ route('onboarding.v2.wizard') }}" class="ob-btn ob-btn-primary">Start a fresh draft</a>
    </div>
</x-layouts.onboarding>
