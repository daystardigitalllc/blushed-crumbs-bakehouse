<div wire:poll.visible.2s="$refresh" class="ob-activity-feed">
    <ul class="ob-activity-list">
        @forelse ($this->events as $event)
            <li wire:key="event-{{ $event->id }}" class="ob-activity-item">
                <span class="ob-activity-time">{{ $event->created_at->format('g:i:s a') }}</span>
                <span class="ob-activity-message">{{ $event->message ?? \Illuminate\Support\Str::headline($event->type) }}</span>
            </li>
        @empty
            <li class="ob-activity-empty">No activity yet.</li>
        @endforelse
    </ul>
</div>
