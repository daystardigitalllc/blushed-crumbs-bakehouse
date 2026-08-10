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
                <span>What do you specialize in?</span>
                <select class="ob-input" wire:model="basicsForm.bakery_type">
                    <option value="">Select one&hellip;</option>
                    @foreach (\App\Livewire\Onboarding\Wizard::BAKERY_TYPE_OPTIONS as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                <div class="ob-field-hint">Helps us pick the right theme and write copy that actually matches what you make.</div>
                @error('basicsForm.bakery_type') <small class="ob-field-error">{{ $message }}</small> @enderror
            </label>
            <label class="ob-field">
                <span>Hours</span>
                <input type="text" class="ob-input" wire:model="basicsForm.hours" placeholder="Mon-Sat 9-5">
            </label>
            <label class="ob-field">
                <span>Location <span class="ob-field-optional">(short description, shown on your site)</span></span>
                <input type="text" class="ob-input" wire:model="basicsForm.location" placeholder="City, State">
            </label>

            <div class="ob-field-hint" style="margin:8px 0 -4px;">Contact details below are optional, but they power your Google listing info and a real "Contact Us" section — without them those stay blank.</div>
            <label class="ob-field">
                <span>Street address</span>
                <input type="text" class="ob-input" wire:model="basicsForm.address_line1" placeholder="123 Main St">
            </label>
            <div class="ob-field-row">
                <label class="ob-field">
                    <span>City</span>
                    <input type="text" class="ob-input" wire:model="basicsForm.city">
                </label>
                <label class="ob-field">
                    <span>State</span>
                    <input type="text" class="ob-input" wire:model="basicsForm.state" placeholder="TN">
                </label>
                <label class="ob-field">
                    <span>ZIP</span>
                    <input type="text" class="ob-input" wire:model="basicsForm.postal_code">
                </label>
            </div>
            <div class="ob-field-row">
                <label class="ob-field">
                    <span>Phone</span>
                    <input type="text" class="ob-input" wire:model="basicsForm.phone" placeholder="(555) 555-5555">
                </label>
                <label class="ob-field">
                    <span>Public contact email</span>
                    <input type="email" class="ob-input" wire:model="basicsForm.contact_email">
                    @error('basicsForm.contact_email') <small class="ob-field-error">{{ $message }}</small> @enderror
                </label>
            </div>

            <label class="ob-field">
                <span>Instagram</span>
                <input type="text" class="ob-input" wire:model="basicsForm.instagram" placeholder="@yourbakery">
            </label>
            <label class="ob-field">
                <span>Facebook</span>
                <input type="text" class="ob-input" wire:model="basicsForm.facebook">
            </label>
            <label class="ob-field">
                <span>Logo <span class="ob-field-optional">(optional, but recommended)</span></span>
                <input type="file" class="ob-input" wire:model="logo" accept="image/*">
                @error('logo') <small class="ob-field-error">{{ $message }}</small> @enderror
                <div wire:loading wire:target="logo" class="ob-field-hint">Uploading&hellip;</div>
                @if ($logo)
                    <img src="{{ $logo->temporaryUrl() }}" alt="Logo preview" class="ob-logo-preview">
                @endif
            </label>

            <div class="ob-field-hint" style="margin:8px 0 -4px;">Got a few real customer quotes? Add up to 3 — totally optional, but it beats an empty reviews section.</div>
            @foreach ($reviewsForm as $i => $review)
                <div class="ob-field-row">
                    <label class="ob-field">
                        <span>Customer name</span>
                        <input type="text" class="ob-input" wire:model="reviewsForm.{{ $i }}.name" placeholder="e.g. Sarah M.">
                    </label>
                    <label class="ob-field" style="flex:2;">
                        <span>Their quote</span>
                        <input type="text" class="ob-input" wire:model="reviewsForm.{{ $i }}.quote" placeholder="What did they say?">
                    </label>
                </div>
            @endforeach

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

        @if ($lowPhotoWarningAcknowledged && $this->uploadedPhotoCount < 5)
            <div class="ob-field-hint" style="background:#fef3c7; border:1px solid #f59e0b; color:#92400e; padding:12px 16px; border-radius:10px; font-weight:500;">
                Sites built from fewer than 5 photos tend to look sparse — the same photo ends up reused across several sections. Add a few more if you have them, or continue and add more later from your dashboard.
            </div>
        @endif

        <button type="button" class="ob-btn ob-btn-primary" wire:click="continueToAnalysis">
            {{ $lowPhotoWarningAcknowledged ? 'Continue anyway' : 'Analyze my files' }}
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
