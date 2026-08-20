<?php

namespace App\Livewire\Onboarding;

use App\Models\Onboarding\OnboardingDraft;
use App\Models\Onboarding\OnboardingDraftItem;
use App\Models\Onboarding\OnboardingFile;
use App\Models\Tenant;
use App\Services\Onboarding\TenantMediaPath;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Step 5's editable review — user-driven only, no polling. Every mutation
 * re-scopes by draft_id (and mount() verifies tenant ownership up front)
 * so an item id alone is never enough to touch another draft's row.
 */
class ReviewPanel extends Component
{
    #[Locked]
    public int $draftId;

    public ?int $logoJustSet = null;

    public function mount(int $draftId): void
    {
        $this->draftId = $draftId;
        $this->draft(); // triggers the tenant-ownership check below
    }

    #[Computed]
    public function draft(): OnboardingDraft
    {
        $draft = OnboardingDraft::findOrFail($this->draftId);
        abort_unless($draft->tenant_id === auth()->user()->tenant_id || auth()->user()->isSuperAdmin(), 403);

        return $draft;
    }

    #[Computed]
    public function tenant(): Tenant
    {
        return Tenant::findOrFail($this->draft()->tenant_id);
    }

    #[Computed]
    public function categories(): Collection
    {
        return $this->itemsOfType('category');
    }

    #[Computed]
    public function products(): Collection
    {
        return $this->itemsOfType('product');
    }

    #[Computed]
    public function galleryImages(): Collection
    {
        return $this->itemsOfType('gallery_image');
    }

    #[Computed]
    public function availableThemes(): array
    {
        return $this->tenant()->onboardingAvailableThemes($this->draft()->basics['selected_plan'] ?? null);
    }

    /**
     * Every theme the tenant could ever pick (minus sweet_elegant's
     * hardcoded Blushed Crumbs exclusivity), each flagged 'locked' when it's
     * not in availableThemes() — i.e. Pro-only and this tenant isn't Pro.
     * Free bakers see the full theme lineup instead of a filtered subset,
     * so browsing what they're missing becomes a soft upsell, same pattern
     * as the admin dashboard's Website Settings theme picker.
     */
    #[Computed]
    public function themesForPicker(): array
    {
        $available = $this->availableThemes();

        return collect($this->tenant()->getAvailableThemesForTenant())
            ->map(fn ($theme) => $theme + ['locked' => !array_key_exists($theme['id'], $available)])
            ->all();
    }

    private function itemsOfType(string $type): Collection
    {
        return OnboardingDraftItem::where('draft_id', $this->draftId)
            ->where('type', $type)
            ->where('status', '!=', 'rejected')
            ->orderBy('sort_order')
            ->get(['id', 'payload_final', 'status', 'source_file_id']);
    }

    public function updateProductField(int $itemId, string $field, string $value): void
    {
        if (!in_array($field, ['name', 'description', 'price_min', 'price_max', 'category'], true)) {
            return;
        }

        $item = OnboardingDraftItem::where('draft_id', $this->draftId)->where('type', 'product')->findOrFail($itemId);
        $payload = $item->payload_final ?? [];
        $payload[$field] = $value !== '' ? $value : null;
        $item->payload_final = $payload;
        $item->status = 'edited';
        $item->save();

        unset($this->products);
    }

    public function rejectItem(int $itemId, string $type): void
    {
        $item = OnboardingDraftItem::where('draft_id', $this->draftId)->where('type', $type)->findOrFail($itemId);
        $item->status = 'rejected';
        $item->save();

        unset($this->categories, $this->products, $this->galleryImages);
    }

    /**
     * Lets the baker override the AI's hero-photo guess. `is_hero` is what
     * ImportDraftJob::applyBackgroundImages() reads to pick the hero-slot
     * image — see DraftSynthesisService::pickHero() for the AI-only path
     * this replaces once a baker makes an explicit choice.
     */
    public function setHero(int $itemId): void
    {
        OnboardingDraftItem::where('draft_id', $this->draftId)
            ->where('type', 'gallery_image')
            ->get()
            ->each(function (OnboardingDraftItem $item) use ($itemId) {
                $payload = $item->payload_final ?? [];
                $isTarget = $item->id === $itemId;
                if (($payload['is_hero'] ?? false) !== $isTarget) {
                    $payload['is_hero'] = $isTarget;
                    $item->payload_final = $payload;
                    $item->save();
                }
            });

        unset($this->galleryImages);
    }

    /**
     * Copies an already-uploaded gallery photo into the draft's private logo
     * slot, same destination Wizard::storeLogo() writes to — so a baker can
     * pick their logo from photos they already uploaded instead of finding
     * and re-uploading a separate cropped file.
     */
    public function setLogoFromFile(int $sourceFileId): void
    {
        $file = OnboardingFile::where('draft_id', $this->draftId)->findOrFail($sourceFileId);
        $draft = $this->draft();

        $dir = TenantMediaPath::draftLogoDir($draft->tenant_id, $draft->id);
        TenantMediaPath::ensureDir($dir);

        foreach (glob($dir . '/*') ?: [] as $existing) {
            @unlink($existing);
        }

        $extension = pathinfo($file->path, PATHINFO_EXTENSION) ?: 'jpg';
        $destination = "{$dir}/logo.{$extension}";

        if (!@copy($file->path, $destination)) {
            return;
        }

        $draft->logo_path = $destination;
        $draft->save();

        unset($this->draft);
        $this->logoJustSet = $sourceFileId;
    }

    public function changeTheme(string $themeId): void
    {
        if (!array_key_exists($themeId, $this->availableThemes())) {
            return;
        }

        $draft = $this->draft();
        $draft->theme_id = $themeId;
        $draft->save();

        unset($this->draft);
    }

    public function render()
    {
        return view('livewire.onboarding.review-panel');
    }
}
