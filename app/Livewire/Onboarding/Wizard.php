<?php

namespace App\Livewire\Onboarding;

use App\Jobs\Onboarding\ImportDraftJob;
use App\Models\Onboarding\OnboardingDraft;
use App\Models\Tenant;
use App\Services\Onboarding\TenantMediaPath;
use Illuminate\Support\Facades\URL;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * The full-page wizard at /onboarding/v2/{draft?}. Real URLs (not query
 * params or session state) so a resume email is a plain deep link, and it
 * stays under /onboarding/* so ResolveTenant's tenant binding still fires.
 *
 * $draftId is #[Locked] — an unlocked Livewire property is client-writable
 * on every request, and an unlocked draft ID here would be a straight IDOR
 * into another tenant's draft. Every child component re-derives and
 * re-verifies tenant ownership independently rather than trusting the
 * parent passed a safe value.
 *
 * Steps only ever advance on a verified backend state (a saved model, a
 * draft status read fresh from the DB), never optimistically — this is
 * what fixes the legacy wizard's silent data loss on a failed request.
 */
class Wizard extends Component
{
    use WithFileUploads;

    #[Locked]
    public int $draftId;

    public string $step = 'basics';

    /**
     * Canonical bakery-type options — shown as the "What do you specialize
     * in?" select on the basics step. An explicit baker-chosen type is a far
     * more reliable signal than inferring one from photos (photo analysis
     * can fail outright — see the Aug 2026 onboarding investigation this was
     * built from), so it flows straight into the AI copy prompt via `basics`
     * and also seeds a type-appropriate fallback if Gemini never runs at
     * all. Keys are shared with DraftSynthesisService::BAKERY_TYPE_DEFAULTS
     * — keep both lists in sync if you add/rename an option.
     */
    public const BAKERY_TYPE_OPTIONS = [
        'cakes' => '🎂 Cakes',
        'cupcakes' => '🧁 Cupcakes',
        'cookies' => '🍪 Cookies',
        'breads' => '🍞 Bread / Sourdough',
        'pastries' => '🥐 Pastries',
        'pies' => '🥧 Pies',
        'cake_pops' => '🍭 Cake Pops',
        'donuts' => '🍩 Donuts',
        'macarons' => '🇫🇷 Macarons',
        'brownies' => '🍫 Brownies',
        'muffins' => '🧁 Muffins',
        'mixed' => '🥖 A Bit of Everything',
    ];

    public array $basicsForm = [
        'business_name' => '',
        'bakery_type' => '',
        'hours' => '',
        'location' => '',
        'phone' => '',
        'contact_email' => '',
        'address_line1' => '',
        'city' => '',
        'state' => '',
        'postal_code' => '',
        'instagram' => '',
        'facebook' => '',
        'selected_plan' => 'free',
    ];

    /**
     * Optional real customer quotes — up to 3. Kept out of $basicsForm since
     * it's an array-of-arrays (Livewire handles wire:model="reviewsForm.0.name"
     * fine either way, but this keeps saveBasics()'s validation rules simpler
     * to read). Left blank by a baker, the reviews section just doesn't
     * render — see Tenant::getDefaultSiteContent()'s 'reviews' => [] comment.
     */
    public array $reviewsForm = [
        ['name' => '', 'quote' => ''],
        ['name' => '', 'quote' => ''],
        ['name' => '', 'quote' => ''],
    ];

    /** A single logo image — Livewire's own uploader is fine here (unlike the bulk uploader, one file has no 20-file/session-lock concerns). */
    public $logo = null;

    public function mount(?OnboardingDraft $draft = null): void
    {
        $tenantId = auth()->user()->tenant_id;

        if ($draft) {
            // Superadmins can open any tenant's draft for support/diagnostics
            // (mirrors EnsureBakerOwnsTenant's dashboard-route bypass) — every
            // other read below derives its tenant from $this->draft rather
            // than auth()->user()->tenant_id, so the wizard actually operates
            // on the draft's tenant instead of the viewing superadmin's own.
            abort_unless($draft->tenant_id === $tenantId || auth()->user()->isSuperAdmin(), 403);
            $this->draftId = $draft->id;
        } else {
            $existing = OnboardingDraft::where('tenant_id', $tenantId)
                ->where('status', '!=', 'imported')
                ->orderByDesc('version')
                ->first();

            $draft = $existing ?? OnboardingDraft::create([
                'tenant_id' => $tenantId,
                'version' => (int) (OnboardingDraft::where('tenant_id', $tenantId)->max('version') ?? 0) + 1,
                'status' => 'collecting',
                'last_activity_at' => now(),
                'resume_token' => \Illuminate\Support\Str::random(48),
            ]);

            $this->redirect(route('onboarding.v2.wizard', ['draft' => $draft->id]), navigate: false);

            return;
        }

        $this->basicsForm = array_merge($this->basicsForm, $draft->basics ?? []);
        if (!empty($draft->basics['reviews']) && is_array($draft->basics['reviews'])) {
            foreach ($draft->basics['reviews'] as $i => $review) {
                if (isset($this->reviewsForm[$i])) {
                    $this->reviewsForm[$i] = array_merge($this->reviewsForm[$i], $review);
                }
            }
        }
        $this->step = $this->stepForStatus($draft);
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
        // Derived from the already-authorized draft (not auth()->user()
        // directly) so a superadmin viewing another tenant's draft operates
        // on *that* tenant, not their own.
        return Tenant::findOrFail($this->draft->tenant_id);
    }

    /** Signed URL the hand-written uploader JS posts each file to (see Phase 2's OnboardingUploadController). */
    #[Computed]
    public function uploadUrl(): string
    {
        return URL::temporarySignedRoute(
            'onboarding.upload.store',
            now()->addMinutes((int) config('onboarding.upload_url_ttl_minutes', 180)),
            ['draft' => $this->draftId]
        );
    }

    /**
     * The tenant's real live site — same URL scheme OnboardingController::
     * publish() uses for the legacy wizard. `/site/{subdomain}` (what the
     * 'done' step used to link to) is the internal storefront *preview*
     * route, not the actual public site the baker's subdomain resolves to.
     */
    #[Computed]
    public function liveSiteUrl(): string
    {
        $tenant = $this->tenant();

        if (!empty($tenant->custom_domain)) {
            $domain = preg_replace('#^https?://#', '', trim($tenant->custom_domain, '/'));

            return 'https://' . $domain;
        }

        $brandDomain = $tenant->brand?->domain ?? 'doughmain.pro';

        return 'https://' . $tenant->subdomain . '.' . $brandDomain;
    }

    public function saveBasics(): void
    {
        $validated = $this->validate([
            'basicsForm.business_name' => 'required|string|max:255',
            'basicsForm.bakery_type' => 'nullable|in:' . implode(',', array_keys(self::BAKERY_TYPE_OPTIONS)),
            'basicsForm.hours' => 'nullable|string|max:255',
            'basicsForm.location' => 'nullable|string|max:255',
            'basicsForm.phone' => 'nullable|string|max:30',
            'basicsForm.contact_email' => 'nullable|email|max:255',
            'basicsForm.address_line1' => 'nullable|string|max:255',
            'basicsForm.city' => 'nullable|string|max:120',
            'basicsForm.state' => 'nullable|string|max:60',
            'basicsForm.postal_code' => 'nullable|string|max:20',
            'basicsForm.instagram' => 'nullable|string|max:255',
            'basicsForm.facebook' => 'nullable|string|max:255',
            'basicsForm.selected_plan' => 'in:free,pro',
            'logo' => 'nullable|image|max:5120',
            'reviewsForm.*.name' => 'nullable|string|max:100',
            'reviewsForm.*.quote' => 'nullable|string|max:500',
        ]);

        $draft = $this->draft();
        $draft->basics = array_merge($validated['basicsForm'], [
            'reviews' => collect($validated['reviewsForm'])
                ->filter(fn ($r) => trim($r['name'] ?? '') !== '' && trim($r['quote'] ?? '') !== '')
                ->values()
                ->all(),
        ]);
        $draft->last_activity_at = now();

        if ($this->logo) {
            $draft->logo_path = $this->storeLogo($draft);
            $this->logo = null;
        }

        $draft->save();

        $this->step = 'upload';
    }

    /**
     * Stored in the draft's own private storage — not public/uploads — so it
     * follows the same "nothing public until approved" rule as every other
     * onboarding upload. ImportDraftJob copies it into public/uploads and
     * sets tenant.logo_path once the baker actually approves the draft.
     */
    private function storeLogo(OnboardingDraft $draft): string
    {
        $dir = TenantMediaPath::draftLogoDir($draft->tenant_id, $draft->id);
        TenantMediaPath::ensureDir($dir);

        foreach (glob($dir . '/*') ?: [] as $existing) {
            @unlink($existing);
        }

        $filename = 'logo.' . $this->logo->getClientOriginalExtension();
        $destination = "{$dir}/{$filename}";

        // Not ->move() — that goes through Symfony's UploadedFile::move(),
        // which calls move_uploaded_file() and only works on a file uploaded
        // in the CURRENT HTTP request. Livewire's temp file was uploaded in a
        // separate prior request and just sits in storage/app/livewire-tmp
        // as an ordinary file by the time saveBasics() runs, so a plain copy
        // is what actually works here (matches Livewire's own documented
        // storeAs() pattern, just against a raw filesystem path instead of a
        // Storage disk, since TenantMediaPath deals entirely in real paths).
        if (!@copy($this->logo->getRealPath(), $destination)) {
            throw new \RuntimeException("Failed to store uploaded logo to {$destination}.");
        }

        return $destination;
    }

    /** Soft floor, not enforced — see continueToAnalysis()'s low-photo nudge. */
    private const MIN_RECOMMENDED_PHOTOS = 5;

    public bool $lowPhotoWarningAcknowledged = false;

    #[Computed]
    public function uploadedPhotoCount(): int
    {
        return \App\Models\Onboarding\OnboardingFile::where('draft_id', $this->draftId)
            ->where('kind', 'image')
            ->count();
    }

    public function continueToAnalysis(): void
    {
        if ($this->draft()->status === 'ready_for_review') {
            $this->step = 'review';

            return;
        }

        if (!$this->lowPhotoWarningAcknowledged && $this->uploadedPhotoCount() < self::MIN_RECOMMENDED_PHOTOS) {
            $this->lowPhotoWarningAcknowledged = true; // next click proceeds regardless

            return;
        }

        $this->step = 'analyzing';
    }

    /**
     * Polled while step === 'analyzing' — the extraction/synthesis pipeline
     * (Phases 3-5) runs entirely in the background. Only fires while the tab
     * is open and visible (wire:poll.visible), which is exactly what makes
     * touching last_activity_at here a reliable "are they still watching"
     * signal for SynthesizeDraftJob's resume-email decision — see Phase 9.
     */
    public function checkProgress(): void
    {
        $draft = $this->draft();
        $draft->last_activity_at = now();
        $draft->save();

        if ($draft->status === 'ready_for_review') {
            $this->step = 'review';
        } elseif ($draft->status === 'failed') {
            $this->step = 'analyzing_failed';
        }
    }

    public function buildSite(): void
    {
        $draft = $this->draft();
        // 'failed' is included so the building_failed screen's "Try again"
        // button (which calls this same method) can actually retry — see
        // ImportDraftJob::handle(), which already tolerates re-entry from a
        // prior failed attempt for exactly this reason.
        abort_unless(in_array($draft->status, ['ready_for_review', 'failed'], true), 403);

        ImportDraftJob::dispatch($draft->id);
        $this->step = 'building';
    }

    /** Polled while step === 'building'. */
    public function checkImport(): void
    {
        $status = $this->draft()->status;

        if ($status === 'imported') {
            $this->step = 'done';
        } elseif ($status === 'failed') {
            $this->step = 'building_failed';
        }
    }

    private function stepForStatus(OnboardingDraft $draft): string
    {
        return match ($draft->status) {
            'collecting' => empty($this->basicsForm['business_name']) ? 'basics' : 'upload',
            'extracting', 'synthesizing' => 'analyzing',
            'ready_for_review' => 'review',
            'importing' => 'building',
            'imported' => 'done',
            // 'failed' is ambiguous on its own — it's set both by
            // SynthesizeDraftJob (analysis stage) and ImportDraftJob (build
            // stage). proposed_content is only ever written by a successful
            // synthesis, so its presence tells us which stage actually
            // failed — otherwise every import-stage failure got mislabeled
            // as an analysis failure on page reload, and its "Try again"
            // button (continueToAnalysis) looped back to 'analyzing' without
            // ever re-dispatching the import, trapping the user.
            'failed' => $draft->proposed_content !== null ? 'building_failed' : 'analyzing_failed',
            default => 'basics',
        };
    }

    public function render()
    {
        return view('livewire.onboarding.wizard')
            ->layout('components.layouts.onboarding', ['title' => 'Build Your Bakery Website', 'progress' => $this->stepProgressPercent()]);
    }

    private function stepProgressPercent(): int
    {
        return match ($this->step) {
            'basics' => 10,
            'upload' => 25,
            'analyzing', 'analyzing_failed' => 55,
            'review' => 75,
            'building', 'building_failed' => 90,
            'done' => 100,
            default => 0,
        };
    }
}
