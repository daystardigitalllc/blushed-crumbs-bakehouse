<?php

namespace App\Services\Onboarding;

use App\Models\Onboarding\OnboardingDraft;
use App\Models\Onboarding\OnboardingFile;
use App\Models\Tenant;
use App\Services\Onboarding\Extraction\GeminiClient;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * The one-text-call synthesis step: turns everything extraction found
 * (product/price detections from photos and menu PDFs, image labels, the
 * baker's typed basics) into a full site_content proposal, a theme pick,
 * canonicalized categories with matched cover images, and a hero image —
 * nothing here writes to public/uploads or any live tenant table. Pure
 * computation over what's passed in; SynthesizeDraftJob does the persisting.
 */
class DraftSynthesisService
{
    public function __construct(private GeminiClient $client)
    {
    }

    /**
     * @return array{site_content:array,theme_id:string,confidence_overall:float,categories:array,products:array,hero_file_id:?int,model_versions:array}
     */
    public function synthesize(OnboardingDraft $draft, Tenant $tenant): array
    {
        $files = OnboardingFile::where('draft_id', $draft->id)
            ->where('status', 'extracted')
            ->get();

        $images = $files->where('kind', 'image');
        $pdfItems = $this->pdfLineItems($files->where('kind', 'pdf'));

        [$categories, $categoryWords] = $this->canonicalizeCategories($pdfItems, $images);
        $categories = $this->attachCoverImages($categories, $categoryWords, $images);

        $heroFile = $this->pickHero($images);
        $themeChoices = $this->availableThemeChoices($draft, $tenant);

        $aiCopy = $this->generateCopy($draft, $tenant, $images, $pdfItems, $categories, $themeChoices);

        $siteContent = $this->mergeSiteContent($tenant, $draft, $aiCopy, $categories);
        $themeId = $this->resolveTheme($aiCopy['theme_id'] ?? null, $themeChoices);

        return [
            'site_content' => $siteContent,
            'theme_id' => $themeId,
            'confidence_overall' => $this->computeConfidence($files),
            'categories' => $categories,
            'products' => $this->buildProducts($pdfItems, $images, $categoryWords),
            'hero_file_id' => $heroFile?->id,
            'model_versions' => [
                'synthesis_model' => (string) config('services.gemini.extraction_model', 'gemini-3.5-flash'),
                'prompt_version' => (string) config('onboarding.ai_prompt_version', 'v1'),
            ],
        ];
    }

    // ─── Category canonicalization + cover images ───

    private const CATEGORY_SYNONYMS = [
        'cake' => 'cakes', 'cakes' => 'cakes', 'wedding cake' => 'cakes', 'wedding cakes' => 'cakes',
        'birthday cake' => 'cakes', 'birthday cakes' => 'cakes',
        'cupcake' => 'cupcakes', 'cupcakes' => 'cupcakes',
        'cookie' => 'cookies', 'cookies' => 'cookies',
        'bread' => 'breads', 'breads' => 'breads', 'sourdough' => 'breads', 'loaf' => 'breads', 'loaves' => 'breads',
        'pastry' => 'pastries', 'pastries' => 'pastries', 'croissant' => 'pastries', 'croissants' => 'pastries', 'danish' => 'pastries',
        'pie' => 'pies', 'pies' => 'pies',
        'donut' => 'donuts', 'donuts' => 'donuts', 'doughnut' => 'donuts', 'doughnuts' => 'donuts',
        'macaron' => 'macarons', 'macarons' => 'macarons',
        'brownie' => 'brownies', 'brownies' => 'brownies',
        'muffin' => 'muffins', 'muffins' => 'muffins',
        'seasonal' => 'seasonal specials', 'special' => 'seasonal specials', 'specials' => 'seasonal specials',
    ];

    /**
     * @return array{0:Collection,1:array<string,array<string>>} ranked category rows plus a
     *   lowercase-key => raw-words-in-that-group map, used again for cover-image matching
     */
    private function canonicalizeCategories(Collection $pdfItems, Collection $images): array
    {
        $raw = $pdfItems->pluck('category')->filter()
            ->merge($images->pluck('ai_result.content_type')->filter()->reject(fn ($t) => in_array($t, ['storefront', 'ambiance', 'other'], true)))
            ->merge($images->pluck('ai_result.product_name')->filter());

        $counts = [];
        $words = [];

        foreach ($raw as $value) {
            $key = strtolower(trim((string) $value));
            if ($key === '') {
                continue;
            }

            $canonicalKey = self::CATEGORY_SYNONYMS[$key] ?? $this->fuzzyMatch($key, array_keys($counts)) ?? $key;
            $counts[$canonicalKey] = ($counts[$canonicalKey] ?? 0) + 1;
            $words[$canonicalKey][] = $key;
        }

        $cap = (int) config('onboarding.synthesis_max_categories', 6);

        $ranked = collect($counts)
            ->sortDesc()
            ->keys()
            ->take($cap)
            ->map(fn ($key) => ['key' => $key, 'name' => ucwords($key), 'count' => $counts[$key]])
            ->values();

        return [$ranked, $words];
    }

    private function fuzzyMatch(string $key, array $existingKeys): ?string
    {
        $threshold = (float) config('onboarding.synthesis_category_similarity_threshold', 85.0);

        foreach ($existingKeys as $existing) {
            similar_text($key, $existing, $percent);
            if ($percent >= $threshold) {
                return $existing;
            }
        }

        return null;
    }

    /**
     * Cover image matched via label intersection: an image counts for a
     * category if any of its labels/content_type/product_name shares a
     * substring with one of that category's raw source words.
     */
    private function attachCoverImages(Collection $categories, array $categoryWords, Collection $images): array
    {
        return $categories->map(function (array $category) use ($categoryWords, $images) {
            $words = $categoryWords[$category['key']] ?? [$category['key']];

            $covers = $images->filter(function (OnboardingFile $file) use ($words) {
                $labels = array_map('strtolower', array_filter(array_merge(
                    $file->ai_labels ?? [],
                    [$file->ai_result['content_type'] ?? null, $file->ai_result['product_name'] ?? null]
                )));

                foreach ($labels as $label) {
                    foreach ($words as $word) {
                        if ($label !== '' && $word !== '' && (str_contains($label, $word) || str_contains($word, $label))) {
                            return true;
                        }
                    }
                }

                return false;
            })->sortByDesc('quality_score')->values();

            $category['cover_file_ids'] = $covers->pluck('id')->all();

            return $category;
        })->all();
    }

    // ─── Hero selection ───

    /**
     * ImageProcessor (Phase 2) already flags is_hero_candidate — landscape,
     * sane aspect ratio, quality_score >= 55. Highest-scoring candidate wins.
     */
    private function pickHero(Collection $images): ?OnboardingFile
    {
        return $images->where('is_hero_candidate', true)->sortByDesc('quality_score')->first();
    }

    // ─── Theme gating ───

    /**
     * Tenant::onboardingAvailableThemes() holds the actual gating rule
     * (starter-vs-pro, mirroring the legacy OnboardingController's own
     * validation-time gating) — shared with the Phase 8 review UI so the
     * picker there can't offer a theme synthesis was never allowed to choose.
     */
    private function availableThemeChoices(OnboardingDraft $draft, Tenant $tenant): array
    {
        return array_keys($tenant->onboardingAvailableThemes($draft->basics['selected_plan'] ?? null));
    }

    private function resolveTheme(?string $proposed, array $choices): string
    {
        if ($proposed !== null && in_array($proposed, $choices, true)) {
            return $proposed;
        }

        return $choices[0] ?? 'rustic_kitchen';
    }

    // ─── Products (from PDF line items + product photos) ───

    private function pdfLineItems(Collection $pdfFiles): Collection
    {
        return $pdfFiles->flatMap(function (OnboardingFile $file) {
            $items = $file->ai_result['items'] ?? [];

            return collect($items)->map(fn ($item) => array_merge($item, ['source_file_id' => $file->id]));
        });
    }

    private function buildProducts(Collection $pdfItems, Collection $images, array $categoryWords): array
    {
        $fromPdf = $pdfItems->map(fn ($item) => [
            'name' => $item['name'] ?? 'Untitled item',
            'description' => $item['description'] ?? null,
            'price_min' => $item['price_min'] ?? null,
            'price_max' => $item['price_max'] ?? null,
            'category' => $this->canonicalCategoryFor($item['category'] ?? null, $categoryWords),
            'source' => 'menu_pdf',
            'source_file_id' => $item['source_file_id'] ?? null,
        ]);

        $fromPhotos = $images
            ->filter(fn (OnboardingFile $file) => !empty($file->ai_result['product_name']))
            ->map(fn (OnboardingFile $file) => [
                'name' => $file->ai_result['product_name'],
                'description' => null,
                'price_min' => $file->ai_result['price'] ?? null,
                'price_max' => $file->ai_result['price'] ?? null,
                'category' => $this->canonicalCategoryFor($file->ai_result['content_type'] ?? null, $categoryWords),
                'source' => 'photo',
                'source_file_id' => $file->id,
            ]);

        // Fill-only-empty in spirit, upfront: same slugged name from both a
        // photo and the menu keeps the menu's (usually more complete) entry.
        return $fromPdf->merge($fromPhotos)
            ->unique(fn ($product) => Str::slug($product['name']))
            ->values()
            ->all();
    }

    private function canonicalCategoryFor(?string $raw, array $categoryWords): ?string
    {
        if (!$raw) {
            return null;
        }

        $key = strtolower(trim($raw));
        foreach ($categoryWords as $canonicalKey => $words) {
            if (in_array($key, $words, true)) {
                return ucwords($canonicalKey);
            }
        }

        return null;
    }

    // ─── Confidence ───

    /**
     * Derived, not self-reported by Gemini (which would just be a hallucinated
     * number) — the real fraction of extracted files that got genuine AI
     * analysis rather than a local-only fallback.
     */
    private function computeConfidence(Collection $files): float
    {
        if ($files->isEmpty()) {
            return 0.0;
        }

        $analyzed = $files->filter(fn (OnboardingFile $f) => ($f->ai_result['source'] ?? null) === 'gemini')->count();

        return round($analyzed / $files->count(), 4);
    }

    // ─── The Gemini call ───

    private function generateCopy(OnboardingDraft $draft, Tenant $tenant, Collection $images, Collection $pdfItems, array $categories, array $themeChoices): array
    {
        $basics = $draft->basics ?? [];

        if (empty($themeChoices) || !$this->client->hasApiKey()) {
            return [];
        }

        $context = [
            'business' => array_merge(['business_name' => $tenant->name], $basics),
            'detected_products' => $pdfItems->take(40)->values()->all(),
            'image_summary' => $images->map(fn (OnboardingFile $f) => [
                'content_type' => $f->ai_result['content_type'] ?? null,
                'labels' => $f->ai_labels,
            ])->take(60)->values()->all(),
            'candidate_categories' => collect($categories)->pluck('name')->all(),
        ];

        $decoded = $this->client->generateJsonWithRepair(
            [['role' => 'user', 'parts' => [['text' => json_encode($context)]]]],
            $this->systemInstruction($themeChoices),
            $this->responseSchema($themeChoices),
            null,
            temperature: (float) config('onboarding.synthesis_temperature', 0.7)
        );

        return $decoded ?? [];
    }

    private function systemInstruction(array $themeChoices): string
    {
        return 'You are writing website copy for a bakery based on their uploaded photos, menu, and business basics '
            . '(all given to you as JSON). Write warm, specific, non-generic marketing copy — reference real details '
            . 'from what was detected when possible. Never invent contact information, hours, or prices not present '
            . 'in the input. Pick the single best-fitting theme_id from exactly this list: ' . implode(', ', $themeChoices) . '.';
    }

    private function responseSchema(array $themeChoices): array
    {
        $textArrayOfObjects = fn (array $properties, array $required) => [
            'type' => 'ARRAY',
            'items' => ['type' => 'OBJECT', 'properties' => $properties, 'required' => $required],
        ];

        return [
            'type' => 'OBJECT',
            'properties' => [
                'theme_id' => ['type' => 'STRING', 'enum' => $themeChoices],
                'hero_subheading' => ['type' => 'STRING'],
                'hero_headline' => ['type' => 'STRING'],
                'hero_cta_primary' => ['type' => 'STRING'],
                'hero_cta_secondary' => ['type' => 'STRING'],
                'highlights' => $textArrayOfObjects(['icon' => ['type' => 'STRING'], 'title' => ['type' => 'STRING'], 'desc' => ['type' => 'STRING']], ['title', 'desc']),
                'promo_headline' => ['type' => 'STRING'],
                'promo_subtext' => ['type' => 'STRING'],
                'how_it_works' => $textArrayOfObjects(['title' => ['type' => 'STRING'], 'desc' => ['type' => 'STRING']], ['title', 'desc']),
                'categories' => $textArrayOfObjects(['title' => ['type' => 'STRING'], 'desc' => ['type' => 'STRING']], ['title', 'desc']),
                'whimsical_title' => ['type' => 'STRING'],
                'whimsical_bullets' => ['type' => 'ARRAY', 'items' => ['type' => 'STRING']],
                'faqs' => $textArrayOfObjects(['q' => ['type' => 'STRING'], 'a' => ['type' => 'STRING']], ['q', 'a']),
                'cta_headline' => ['type' => 'STRING'],
                'cta_subtext' => ['type' => 'STRING'],
                'cta_btn_text' => ['type' => 'STRING'],
                'about_title' => ['type' => 'STRING'],
                'about_bio' => ['type' => 'STRING'],
                'seo_title' => ['type' => 'STRING'],
                'seo_description' => ['type' => 'STRING'],
            ],
            'required' => ['theme_id', 'hero_headline', 'about_bio', 'seo_title', 'seo_description'],
        ];
    }

    /**
     * Every siteContentSchema() key that has a static default is guaranteed
     * non-empty: Gemini's copy wins where present, the baker's own typed
     * basics win for contact fields (never AI-invented), the static default
     * fills anything still missing. Image/video URL keys and `menu` have no
     * default and stay absent — they're resolved from real media at import
     * (Phase 6), not fabricated here.
     */
    private function mergeSiteContent(Tenant $tenant, OnboardingDraft $draft, array $aiCopy, array $categories): array
    {
        $basics = $draft->basics ?? [];
        $defaults = Tenant::getDefaultSiteContent($basics['business_name'] ?? $tenant->name);

        $contactFromBasics = array_filter([
            'contact_hours' => $basics['hours'] ?? null,
            'contact_location' => $basics['location'] ?? null,
            'contact_instagram' => $basics['instagram'] ?? null,
            'contact_facebook' => $basics['facebook'] ?? null,
        ], fn ($v) => $v !== null && $v !== '');

        // Only let Gemini's copy override a default when it actually said
        // something — a blank/empty field from the model must never clobber
        // a perfectly good static default.
        $copy = array_filter($aiCopy, function ($value, $key) {
            if ($key === 'theme_id') {
                return false;
            }

            return is_array($value) ? !empty($value) : trim((string) $value) !== '';
        }, ARRAY_FILTER_USE_BOTH);

        $merged = array_merge($defaults, $contactFromBasics, $copy);

        if (empty($merged['categories']) && !empty($categories)) {
            $merged['categories'] = collect($categories)->map(fn ($c) => [
                'title' => $c['name'],
                'desc' => '',
            ])->all();
        }

        return $merged;
    }
}
