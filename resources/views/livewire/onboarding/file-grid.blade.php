<div class="ob-file-grid">
    @forelse ($this->files as $file)
        <div wire:key="file-{{ $file->id }}" class="ob-file-tile ob-file-tile--{{ $file->status }}">
            <div class="ob-file-tile-icon">
                @if ($file->kind === 'pdf')
                    📄
                @else
                    🖼️
                @endif
            </div>
            <div class="ob-file-tile-name" title="{{ $file->original_filename }}">{{ $file->original_filename }}</div>
            <div class="ob-file-tile-status">
                @switch($file->status)
                    @case('pending') Queued @break
                    @case('extracting') Analyzing&hellip; @break
                    @case('extracted') Ready @break
                    @case('failed') Couldn't process @break
                    @case('unsupported') Unsupported format @break
                    @case('duplicate') Already added @break
                    @default {{ ucfirst($file->status) }}
                @endswitch
            </div>
        </div>
    @empty
        <p class="ob-file-grid-empty">Drop your photos and menu here to get started.</p>
    @endforelse
</div>
