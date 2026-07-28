<?php

namespace App\Livewire\Onboarding;

use App\Models\Onboarding\OnboardingDraft;
use App\Models\Onboarding\OnboardingDraftItem;
use App\Models\Tenant;
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

    public function mount(int $draftId): void
    {
        $this->draftId = $draftId;
        $this->draft(); // triggers the tenant-ownership check below
    }

    #[Computed]
    public function draft(): OnboardingDraft
    {
        $draft = OnboardingDraft::findOrFail($this->draftId);
        abort_unless($draft->tenant_id === auth()->user()->tenant_id, 403);

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
