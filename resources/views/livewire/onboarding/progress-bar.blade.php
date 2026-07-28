<div wire:poll.visible.2s="$refresh" class="ob-progress-widget">
    <div class="ob-progress-track">
        <div class="ob-progress-fill" style="width: {{ $this->percentComplete }}%"></div>
    </div>
    <p class="ob-progress-label">
        {{ $this->percentComplete }}% processed
        @php($counts = $this->counts)
        @if (($counts['failed'] ?? 0) > 0)
            &middot; {{ $counts['failed'] }} need attention
        @endif
    </p>
</div>
