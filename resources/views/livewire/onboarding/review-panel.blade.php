<div class="ob-review">
    <section class="ob-review-section">
        <h2>Theme</h2>
        <div class="ob-theme-grid">
            @foreach ($this->availableThemes as $key => $theme)
                <button
                    type="button"
                    wire:key="theme-{{ $key }}"
                    wire:click="changeTheme('{{ $key }}')"
                    class="ob-theme-swatch {{ $this->draft->theme_id === $key ? 'ob-theme-swatch--selected' : '' }}"
                    style="background: {{ $theme['preview_bg'] }}; border-color: {{ $theme['preview_accent'] }};"
                >
                    <span style="color: {{ $theme['preview_accent'] }}">{{ $theme['name'] }}</span>
                </button>
            @endforeach
        </div>
    </section>

    <section class="ob-review-section">
        <h2>Categories ({{ $this->categories->count() }})</h2>
        <div class="ob-chip-row">
            @forelse ($this->categories as $category)
                <span wire:key="category-{{ $category->id }}" class="ob-chip">
                    {{ $category->payload_final['name'] ?? 'Untitled' }}
                </span>
            @empty
                <p class="ob-review-empty">No categories detected yet.</p>
            @endforelse
        </div>
    </section>

    <section class="ob-review-section">
        <h2>Products ({{ $this->products->count() }})</h2>
        <div class="ob-product-list">
            @forelse ($this->products as $product)
                <div wire:key="product-{{ $product->id }}" class="ob-product-row">
                    <input
                        type="text"
                        class="ob-input"
                        value="{{ $product->payload_final['name'] ?? '' }}"
                        wire:change="updateProductField({{ $product->id }}, 'name', $event.target.value)"
                        aria-label="Product name"
                    >
                    <input
                        type="number"
                        step="0.01"
                        class="ob-input ob-input--price"
                        value="{{ $product->payload_final['price_min'] ?? '' }}"
                        wire:change="updateProductField({{ $product->id }}, 'price_min', $event.target.value)"
                        aria-label="Price"
                        placeholder="Price"
                    >
                    <button type="button" class="ob-btn-icon" wire:click="rejectItem({{ $product->id }}, 'product')" aria-label="Remove product">
                        &times;
                    </button>
                </div>
            @empty
                <p class="ob-review-empty">No products detected yet.</p>
            @endforelse
        </div>
    </section>

    <section class="ob-review-section">
        <h2>Gallery ({{ $this->galleryImages->count() }})</h2>
        <p class="ob-field-hint">Not loving the AI's pick for hero photo or logo? Set your own below.</p>
        @if ($logoJustSet)
            <p class="ob-field-hint" style="color:#15803d; font-weight:600;">Logo updated ✓</p>
        @endif
        <div class="ob-gallery-grid">
            @forelse ($this->galleryImages as $image)
                <div wire:key="gallery-{{ $image->id }}" class="ob-gallery-tile">
                    @if ($image->source_file_id)
                        <img
                            src="{{ route('onboarding.v2.file.preview', ['file' => $image->source_file_id, 'derivative' => 'thumb']) }}"
                            alt="{{ $image->payload_final['alt_text'] ?? '' }}"
                            loading="lazy"
                        >
                    @endif
                    @if ($image->payload_final['is_hero'] ?? false)
                        <span class="ob-gallery-hero-badge">Hero</span>
                    @endif
                    <button type="button" class="ob-btn-icon ob-gallery-remove" wire:click="rejectItem({{ $image->id }}, 'gallery_image')" aria-label="Remove photo">
                        &times;
                    </button>
                    <div class="ob-gallery-tile-actions">
                        @unless ($image->payload_final['is_hero'] ?? false)
                            <button type="button" class="ob-gallery-tile-btn" wire:click="setHero({{ $image->id }})">Set as hero</button>
                        @endunless
                        @if ($image->source_file_id)
                            <button type="button" class="ob-gallery-tile-btn" wire:click="setLogoFromFile({{ $image->source_file_id }})">Use as logo</button>
                        @endif
                    </div>
                </div>
            @empty
                <p class="ob-review-empty">No photos to review yet.</p>
            @endforelse
        </div>
    </section>
</div>
