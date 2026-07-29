@php($counts = $this->counts)
@php($isWorking = $this->percentComplete === 0 && $this->extractingCount > 0)
<div wire:poll.visible.2s="$refresh" class="ob-progress-widget">
    <div class="ob-progress-track">
        <div
            class="ob-progress-fill {{ $isWorking ? 'ob-progress-fill--active' : '' }}"
            style="width: {{ $isWorking ? 100 : $this->percentComplete }}%"
        ></div>
    </div>
    <p class="ob-progress-label">
        @if ($isWorking)
            Analyzing {{ $this->extractingCount }} {{ \Illuminate\Support\Str::plural('file', $this->extractingCount) }} with AI&hellip; this can take up to a minute
        @else
            {{ $this->percentComplete }}% processed
        @endif
        @if (($counts['failed'] ?? 0) > 0)
            &middot; {{ $counts['failed'] }} need attention
        @endif
    </p>
</div>
