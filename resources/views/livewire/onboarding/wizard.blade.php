<div class="ob-card ob-card--wide">
    @if ($step === 'basics')
        <h1>Tell us about your bakery</h1>
        <p class="ob-subtitle">Just the basics — everything else comes from your photos and menu.</p>

        <form wire:submit="saveBasics" class="ob-form">
            <label class="ob-field">
                <span>Business name</span>
                <input type="text" class="ob-input" wire:model="basicsForm.business_name" required>
                @error('basicsForm.business_name') <small class="ob-field-error">{{ $message }}</small> @enderror
            </label>
            <label class="ob-field">
                <span>Hours</span>
                <input type="text" class="ob-input" wire:model="basicsForm.hours" placeholder="Mon-Sat 9-5">
            </label>
            <label class="ob-field">
                <span>Location</span>
                <input type="text" class="ob-input" wire:model="basicsForm.location" placeholder="City, State">
            </label>
            <label class="ob-field">
                <span>Instagram</span>
                <input type="text" class="ob-input" wire:model="basicsForm.instagram" placeholder="@yourbakery">
            </label>
            <label class="ob-field">
                <span>Facebook</span>
                <input type="text" class="ob-input" wire:model="basicsForm.facebook">
            </label>

            <button type="submit" class="ob-btn ob-btn-primary" wire:loading.attr="disabled" wire:target="saveBasics">
                Continue
            </button>
        </form>
    @elseif ($step === 'upload')
        <h1>Upload everything you've got</h1>
        <p class="ob-subtitle">Photos, menu PDFs, price lists, flyers — drop in as much as you have. We'll sort it out.</p>

        <div
            class="ob-dropzone"
            x-data
            x-init="window.initOnboardingUploader($el)"
            data-upload-url="{{ $this->uploadUrl }}"
        >
            <p>Drag &amp; drop files here, or</p>
            <label class="ob-btn ob-btn-secondary">
                Choose files
                <input type="file" multiple accept="image/*,application/pdf" class="ob-dropzone-input" hidden>
            </label>
            <div class="ob-dropzone-uploads"></div>
        </div>

        <livewire:onboarding.file-grid :draft-id="$draftId" wire:key="file-grid-{{ $draftId }}" />

        <button type="button" class="ob-btn ob-btn-primary" wire:click="continueToAnalysis">
            Analyze my files
        </button>
    @elseif ($step === 'analyzing')
        <div wire:poll.visible.2s="checkProgress">
            @if ($this->draft->status === 'synthesizing')
                <h1>Putting your website together&hellip;</h1>
                <p class="ob-subtitle">Your photos and menu are read — now writing your site copy and picking categories. Almost there.</p>
            @else
                <h1>Reading your photos and menu&hellip;</h1>
                <p class="ob-subtitle">This runs in the background — feel free to leave this open while it works.</p>
            @endif

            <livewire:onboarding.progress-bar :draft-id="$draftId" wire:key="progress-{{ $draftId }}" />
            <livewire:onboarding.activity-feed :draft-id="$draftId" wire:key="activity-{{ $draftId }}" />
        </div>
    @elseif ($step === 'analyzing_failed')
        <h1>Something went wrong</h1>
        <p class="ob-subtitle">We hit a snag analyzing your files. Your uploads are safe — try again in a moment.</p>
        <button type="button" class="ob-btn ob-btn-primary" wire:click="continueToAnalysis">Try again</button>
    @elseif ($step === 'review')
        <h1>Here's what we found</h1>
        <p class="ob-subtitle">Edit anything before we build your site — nothing goes live until you approve it.</p>

        <livewire:onboarding.review-panel :draft-id="$draftId" wire:key="review-{{ $draftId }}" />

        <button type="button" class="ob-btn ob-btn-primary" wire:click="buildSite" wire:loading.attr="disabled" wire:target="buildSite">
            Build My Website
        </button>
    @elseif ($step === 'building')
        <div wire:poll.visible.2s="checkImport">
            <h1>Building your site&hellip;</h1>
            <p class="ob-subtitle">Almost there.</p>
        </div>
    @elseif ($step === 'building_failed')
        <h1>Import hit a snag</h1>
        <p class="ob-subtitle">Nothing went live — your review and uploads are untouched. Try building again.</p>
        <button type="button" class="ob-btn ob-btn-primary" wire:click="buildSite">Try again</button>
    @elseif ($step === 'done')
        <h1>Your website is live! 🎉</h1>
        <p class="ob-subtitle">Congratulations — {{ $this->tenant->name }} is ready for customers.</p>
        <a href="{{ $this->liveSiteUrl }}" class="ob-btn ob-btn-primary">View my site</a>
    @endif
</div>

<script src="{{ asset('js/onboarding-uploader.js') }}" defer></script>
