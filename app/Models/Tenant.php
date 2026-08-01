<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Contracts\Tenant as TenancyContract;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Concerns\HasInternalKeys;
use Stancl\Tenancy\Database\Concerns\InvalidatesResolverCache;
use Stancl\Tenancy\Database\Concerns\TenantRun;

class Tenant extends Model implements TenancyContract
{
    use HasFactory;
    use HasDomains;
    use HasInternalKeys;
    use InvalidatesResolverCache;
    use TenantRun;

    /**
     * stancl/tenancy support — this app uses its own auto-increment integer
     * primary key (not the package's default UUID), single database mode.
     */
    public function getTenantKeyName(): string
    {
        return 'id';
    }

    public function getTenantKey()
    {
        return $this->getAttribute($this->getTenantKeyName());
    }

    protected $fillable = [
        'name',
        'slug',
        'domain',
        'subdomain',
        'custom_domain',
        'custom_domain_status',
        'custom_domain_token',
        'custom_domain_verified_at',
        'custom_domain_last_checked_at',
        'custom_domain_last_error',
        'brand_id',
        'owner_name',
        'email',
        'phone',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'postal_code',
        'country_code',
        'business_type',
        'website_url',
        'timezone',
        'plan_tier',
        'stripe_customer_id',
        'stripe_subscription_id',
        'theme_id',
        'pending_pro_theme_id',
        'logo_path',
        'gallery_images',
        'instagram_url',
        'facebook_url',
        'payment_settings',
        'form_schema',
        'site_content',
        'section_settings',
        'booking_settings',
        'ai_generated_content',
        'onboarding_completed',
        'onboarding_flow_version',
        'onboarding_started_at',
        'onboarding_completed_at',
        'active_onboarding_draft_id',
        'max_reviews_display',
        'is_active',
        'is_demo',
    ];

    protected $casts = [
        'payment_settings' => 'array',
        'form_schema' => 'array',
        'site_content' => 'array',
        'section_settings' => 'array',
        'booking_settings' => 'array',
        'ai_generated_content' => 'array',
        'gallery_images' => 'array',
        'onboarding_completed' => 'boolean',
        'onboarding_started_at' => 'datetime',
        'onboarding_completed_at' => 'datetime',
        'is_active' => 'boolean',
        'is_demo' => 'boolean',
        'custom_domain_verified_at' => 'datetime',
        'custom_domain_last_checked_at' => 'datetime',
    ];

    /**
     * The canonical set of site_content keys in real use across the app —
     * codified so it stops being tribal knowledge. Not every key has a
     * default in getDefaultSiteContent() (the image/video URL keys and
     * `menu` are intentionally left unset there — they're resolved from
     * actual uploaded/generated media, not invented as text).
     */
    public static function siteContentSchema(): array
    {
        return [
            'hero_subheading',
            'hero_headline',
            'hero_cta_primary',
            'hero_cta_secondary',
            'hero_bg_url',
            'highlights',
            'promo_video_url',
            'promo_headline',
            'promo_subtext',
            'promo_bg_image_url',
            'how_it_works',
            'categories',
            'whimsical_title',
            'whimsical_bullets',
            'whimsical_image_url',
            'reviews',
            'faqs',
            'menu',
            'cta_banner_url',
            'cta_bg_image_url',
            'cta_headline',
            'cta_subtext',
            'cta_btn_text',
            'featured_gallery_title',
            'featured_gallery_images',
            'about_title',
            'about_bio',
            'contact_hours',
            'contact_location',
            'contact_instagram',
            'contact_facebook',
            'seo_title',
            'seo_description',
            'policy_deposit_percentage',
            'policy_late_fee_percentage',
            'policy_delivery_base_fee',
            'policy_delivery_per_mile',
            'policy_delivery_change_fee',
            'policy_pickup_hours',
            'policy_closed_days',
            'policy_extra_layer_fee',
        ];
    }

    public static function getDefaultSiteContent(?string $bakeryName = null)
    {
        $name = $bakeryName ?? 'Artisanal Bakehouse';
        return [
            'hero_subheading' => 'Order For Any Occasion',
            'hero_headline' => $name,
            'hero_cta_primary' => 'Order Now',
            'hero_cta_secondary' => 'Our Treats',
            'highlights' => [
                ['icon' => '🎂', 'title' => 'Easy Catering', 'desc' => 'Add custom baked goods to any occasion'],
                ['icon' => '🚚', 'title' => 'Freshly Baked', 'desc' => 'Made to order right before your event'],
                ['icon' => '📦', 'title' => 'Local Delivery', 'desc' => 'Flexible pickup & delivery options'],
                ['icon' => '💖', 'title' => 'Baked with Love', 'desc' => 'Cottage bakery crafted with care'],
            ],
            'promo_video_url' => '',
            'promo_headline' => 'Special Custom Bakery Orders!',
            'promo_subtext' => 'Order online directly from our kitchen for your upcoming celebration.',
            'how_it_works' => [
                ['title' => 'Pick Your Date & Flavors', 'desc' => 'Use our custom ordering form to choose your size, cake flavor, frosting, and upload your inspiration images.'],
                ['title' => 'Approve Design & Deposit', 'desc' => 'Receive your custom invoice & quote via email. Place a deposit to lock in your date on our calendar.'],
                ['title' => 'Fresh Pickup or Delivery', 'desc' => 'We bake your creation fresh right before your event. Pick up at our kitchen or get venue delivery!'],
            ],
            'whimsical_title' => 'Whimsical Creations for Every Milestone',
            'whimsical_bullets' => [
                'Custom Wedding Cakes: Elegant, timeless, and tailored entirely to your love story.',
                'Birthday & Party Cakes: From whimsical children\'s themes to sleek, modern adult designs.',
                'Anniversary Cakes: Recommence your vows with a beautiful, nostalgic dessert.',
                'Signature Sheet Cakes: Perfect for larger crowds, school events, or casual get-togethers.',
                'Gourmet Treats: Custom cupcakes, cake pops, and party dessert boxes.'
            ],
            'reviews' => [
                ['name' => 'Sarah M.', 'quote' => 'Absolutely breathtaking work!! The detail put into this cake was insane and it tasted unbelievable!', 'stars' => 5],
                ['name' => 'Emily R.', 'quote' => 'Ordered a custom cake for my child\'s birthday. The cake was so moist & icing wasn\'t too sweet! Pick up was super easy.', 'stars' => 5],
                ['name' => 'Jessica K.', 'quote' => 'Not only was I extremely shocked at how cute this cake was, I was truly SO surprised with how delicious it was!', 'stars' => 5],
            ],
            'faqs' => [
                ['q' => '📅 How far in advance should I order?', 'a' => 'We require at least 3 days advance notice for custom orders. For weddings and large multi-tier events, we recommend booking 2-4 weeks in advance.'],
                ['q' => '💳 What is the deposit requirement?', 'a' => 'A deposit is required at booking to secure your date. Remaining balance is due prior to pickup or delivery.'],
                ['q' => '⚠️ Allergy Information', 'a' => 'Please disclose all food allergies during checkout so we can accommodate your dietary needs!'],
            ],
            'cta_banner_url' => '',
            'cta_headline' => 'Ready For Your Custom Cake?',
            'cta_subtext' => 'Order your custom baking creation now',
            'cta_btn_text' => 'Order Now',
            'featured_gallery_title' => 'Featured Creations',
            'featured_gallery_images' => [],
            'about_title' => 'About Our Bakery',
            'about_bio' => 'Welcome to ' . $name . '! We specialize in custom artisanal cakes, gourmet treats, and unforgettable dessert experiences crafted with premium ingredients and passion.',
            'contact_hours' => 'Mon-Sat: 8:00 AM - 6:00 PM | Sun: Closed',
            'contact_location' => 'Local Delivery & Pickup Available',
            'contact_instagram' => '',
            'contact_facebook' => '',
            // Policy page numbers/facts — the only parts of the (otherwise
            // shared, hardcoded) policy page that actually vary per bakery.
            // Defaults below match the original hardcoded copy exactly, so
            // no tenant's live page changes until they edit these.
            'policy_deposit_percentage' => '50',
            'policy_late_fee_percentage' => '10',
            'policy_delivery_base_fee' => '30',
            'policy_delivery_per_mile' => '2',
            'policy_delivery_change_fee' => '15',
            'policy_pickup_hours' => '10:00am – 4:00pm',
            'policy_closed_days' => 'Sundays or Mondays',
            'policy_extra_layer_fee' => '20',
        ];
    }

    public function getSiteContent($key, $default = null)
    {
        $defaults = self::getDefaultSiteContent($this->name);
        $content = $this->site_content ?? $defaults;
        $val = data_get($content, $key);
        if ($val !== null && $val !== '') {
            return $val;
        }
        return data_get($defaults, $key, $default);
    }

    public static function getDefaultSectionSettings()
    {
        return [
            'hero' => ['id' => 'hero', 'name' => 'Hero Banner', 'enabled' => true, 'order' => 1],
            // Order 1.5, not 2 — sits between hero and highlights even for
            // tenants whose section_settings were already persisted before
            // this section existed (getOrderedSections() only backfills
            // missing keys, it doesn't renumber ones a tenant already saved).
            'about' => ['id' => 'about', 'name' => 'About / Our Story', 'enabled' => true, 'order' => 1.5],
            'highlights' => ['id' => 'highlights', 'name' => 'Trust Highlights Bar', 'enabled' => true, 'order' => 2],
            'promo_video' => ['id' => 'promo_video', 'name' => 'Video/Image Promo Banner', 'enabled' => true, 'order' => 3],
            'categories' => ['id' => 'categories', 'name' => 'Category Showcase Grid', 'enabled' => false, 'order' => 4],
            'whimsical' => ['id' => 'whimsical', 'name' => 'Whimsical Creations & Specialties', 'enabled' => true, 'order' => 5],
            'how_it_works' => ['id' => 'how_it_works', 'name' => 'How Ordering Works (3 Steps)', 'enabled' => true, 'order' => 6],
            'reviews' => ['id' => 'reviews', 'name' => 'Customer Reviews & Social Proof', 'enabled' => true, 'order' => 7],
            'faq' => ['id' => 'faq', 'name' => 'FAQ & Bakery Policies', 'enabled' => true, 'order' => 8],
            'cta_banner' => ['id' => 'cta_banner', 'name' => 'Footer Booking CTA Banner', 'enabled' => true, 'order' => 9],
            'featured_gallery' => ['id' => 'featured_gallery', 'name' => 'Featured Photos Gallery', 'enabled' => false, 'order' => 10],
        ];
    }

    public function getOrderedSections()
    {
        $sections = $this->section_settings ?? self::getDefaultSectionSettings();

        // Backfill any section types added after this tenant's settings were last saved
        // (e.g. a new section shipped to the platform) so it shows up without requiring
        // the baker to re-save Page Builder first.
        foreach (self::getDefaultSectionSettings() as $key => $default) {
            if (!isset($sections[$key])) {
                $sections[$key] = $default;
            }
        }

        uasort($sections, function ($a, $b) {
            return ($a['order'] ?? 0) <=> ($b['order'] ?? 0);
        });
        return $sections;
    }

    /**
     * Resolve the storefront Blade view for a given page under this tenant's
     * theme, falling back to sweet_elegant for any theme that doesn't have
     * its own template yet (or a bad/missing theme_id).
     */
    public function themeView(string $page): string
    {
        $view = "storefront.themes.{$this->theme_id}.{$page}";
        return view()->exists($view) ? $view : "storefront.themes.sweet_elegant.{$page}";
    }

    /**
     * The asset path for this tenant's theme-specific CSS (public/css/style.css
     * split per theme so a storefront page only downloads its own theme's
     * rules plus the shared base, instead of every theme's CSS on every
     * page). Same missing/bad theme_id fallback as themeView().
     */
    public function themeCssPath(): string
    {
        $path = "css/themes/{$this->theme_id}.css";
        return file_exists(public_path($path)) ? $path : 'css/themes/sweet_elegant.css';
    }

    /**
     * The tenant's one canonical public URL: their verified custom domain
     * if they have one, otherwise their {subdomain}.{brand domain}. Used
     * anywhere we need to point *at* the live site (redirects, "view site"
     * links, canonical tags) so there's a single source of truth instead of
     * each caller re-deriving it.
     */
    public function publicUrl(string $path = ''): string
    {
        if ($this->custom_domain && $this->custom_domain_status === 'verified') {
            $host = $this->custom_domain;
        } else {
            $brandDomain = $this->brand?->domain ?? 'doughmain.pro';
            $host = $this->subdomain . '.' . $brandDomain;
        }

        $path = $path ? '/' . ltrim($path, '/') : '';

        return 'https://' . strtolower($host) . $path;
    }

    /**
     * "123 Main St, Nashville, TN 37201" (or a shorter version if some
     * fields are missing) — the human-readable NAP address line every
     * storefront footer renders. Google (and users) want the same
     * name/address/phone visible as page text, not just inside JSON-LD;
     * this is the single source both use. Null when there's nothing on
     * file yet, so the footer partial can skip rendering entirely.
     */
    public function napAddressLine(): ?string
    {
        $cityStateZip = trim(collect([
            trim(($this->city ?: '') . ($this->city && $this->state ? ', ' . $this->state : ($this->state ?: ''))),
            $this->postal_code,
        ])->filter()->implode(' '));

        $line = collect([$this->address_line1, $cityStateZip])->filter()->implode(', ');

        return $line !== '' ? $line : null;
    }

    /**
     * "{city}, {state}" (or just city, or null) — the fragment every
     * seoTitle()/seoDescription()/localBusinessSchema() variant below builds
     * around, so a baker who's filled in an address ranks for
     * "[service] in [city]" searches instead of just their business name.
     */
    protected function locationLabel(): ?string
    {
        if ($this->city && $this->state) {
            return "{$this->city}, {$this->state}";
        }

        return $this->city ?: null;
    }

    /**
     * Location-aware <title> per storefront page. The homepage prefers the
     * AI-generated seo_title from onboarding (App\Services\AiContentService/
     * DraftSynthesisService already produce one for every tenant; nothing
     * ever read it back out until now) since that's hand-tuned per bakery,
     * then falls back to the location-aware pattern, then the baker's own
     * hero copy when there's no city on file yet.
     */
    public function seoTitle(string $page = 'home'): string
    {
        $name = $this->name ?: 'Bakery';
        $location = $this->locationLabel();

        return match ($page) {
            'about' => $location
                ? "About {$name} | Bakery in {$location}"
                : "About Us | {$name}",
            'menu' => $location
                ? "Menu & Pricing | {$name} in {$location}"
                : "Menu & Pricing | {$name}",
            'gallery' => $location
                ? "Cake Gallery | {$name} in {$location}"
                : "Gallery | {$name}",
            'policy' => "Bakery Policy & Order Terms | {$name}",
            default => $this->getSiteContent('seo_title')
                ?: ($location
                    ? "{$name} | Custom Cakes & Bakery in {$location}"
                    : "{$name} | " . $this->getSiteContent('hero_subheading', 'Where Every Celebration Gets Its Sweet Ending')),
        };
    }

    /**
     * Location-aware meta description per storefront page. Same
     * seo_description-first rule as seoTitle() for the homepage.
     */
    public function seoDescription(string $page = 'home'): string
    {
        $name = $this->name ?: 'our bakery';
        $inLocation = ($loc = $this->locationLabel()) ? " in {$loc}" : '';

        return match ($page) {
            'about' => "Learn about {$name}{$inLocation} — our founder story, our baker, and our passion for custom cakes.",
            'menu' => "Explore the menu, cake flavors, and pricing at {$name}{$inLocation}. Order custom cakes online.",
            'gallery' => "Browse custom cake designs and creations from {$name}{$inLocation}.",
            'policy' => "Official order terms, payment details, pickup hours, delivery rules, and allergen disclosure for {$name}.",
            default => $this->getSiteContent('seo_description')
                ?: $this->getSiteContent('about_bio')
                ?: "Custom artisanal cakes, cupcakes, and treat boxes from {$name}{$inLocation}. Order custom cakes online with ease.",
        };
    }

    /**
     * LocalBusiness/Bakery JSON-LD for the storefront <head> — built only
     * from real tenant data (address, phone, socials, review ratings), never
     * invented values like a guessed price range, so it stays accurate for
     * whatever a baker has actually filled in.
     */
    public function localBusinessSchema(): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Bakery',
            'name' => $this->name,
            'url' => $this->publicUrl(),
        ];

        if ($this->logo_path) {
            $schema['image'] = asset($this->logo_path);
        }
        if ($this->phone) {
            $schema['telephone'] = $this->phone;
        }

        // Only emit an address once there's a real street or city on file —
        // otherwise this would just be a bare {addressCountry: "US"} object,
        // which looks like real data to a schema validator but isn't.
        if ($this->address_line1 || $this->city) {
            $schema['address'] = array_filter([
                '@type' => 'PostalAddress',
                'streetAddress' => $this->address_line1,
                'addressLocality' => $this->city,
                'addressRegion' => $this->state,
                'postalCode' => $this->postal_code,
                'addressCountry' => $this->country_code ?: 'US',
            ]);
        }

        // Explicitly declares "we serve this city" to search engines — the
        // schema.org mechanism for local-search relevance that doesn't
        // require a dedicated landing page per city. Only the tenant's own
        // city, since that's the only service area we actually have data
        // for; inventing a list of nearby towns would misrepresent reach.
        if ($this->city) {
            $schema['areaServed'] = array_filter([
                '@type' => 'City',
                'name' => $this->city,
                'containedInPlace' => $this->state ? [
                    '@type' => 'State',
                    'name' => $this->state,
                ] : null,
            ]);
        }

        $sameAs = array_values(array_filter([$this->instagram_url, $this->facebook_url]));
        if ($sameAs) {
            $schema['sameAs'] = $sameAs;
        }

        $reviewStats = $this->reviews()->selectRaw('COUNT(*) as cnt, AVG(rating) as avg_rating')->first();
        if ($reviewStats && $reviewStats->cnt > 0) {
            $schema['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => round((float) $reviewStats->avg_rating, 1),
                'reviewCount' => (int) $reviewStats->cnt,
            ];
        }

        return $schema;
    }

    /**
     * FAQPage JSON-LD for the homepage — reuses the same faqs site_content
     * every theme's FAQ accordion already renders, so this only ever mirrors
     * what's actually visible on the page (Google penalizes structured data
     * that doesn't match on-page content). Returns null whenever there's
     * nothing to mark up: the FAQ Page Builder section is toggled off, there
     * are no real Q&A pairs, or the active theme doesn't render an FAQ
     * section at all (country_farmhouse doesn't, as of this writing).
     */
    public function faqPageSchema(): ?array
    {
        if ($this->theme_id === 'country_farmhouse') {
            return null;
        }

        if (!($this->getOrderedSections()['faq']['enabled'] ?? false)) {
            return null;
        }

        $faqs = collect($this->getSiteContent('faqs', []))
            ->filter(fn ($faq) => !empty($faq['q']) && !empty($faq['a']))
            ->values();

        if ($faqs->isEmpty()) {
            return null;
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $faqs->map(fn ($faq) => [
                '@type' => 'Question',
                'name' => trim(strip_tags($faq['q'])),
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => trim(strip_tags($faq['a'])),
                ],
            ])->all(),
        ];
    }

    /**
     * Get Starter (Free) themes available for onboarding.
     */
    public static function getStarterThemes(): array
    {
        $all = static::getAllThemes();
        $starterKeys = ['rustic_kitchen', 'modern_bakery', 'country_farmhouse'];
        $starter = [];
        foreach ($starterKeys as $key) {
            if (isset($all[$key])) {
                $starter[$key] = $all[$key];
            }
        }
        return $starter;
    }

    /**
     * Get themes available to this specific tenant.
     * Sweet & Elegant is exclusive to Blushed Crumbs (custom-built for them).
     */
    public function getAvailableThemesForTenant(): array
    {
        $all = static::getAllThemes();

        // Sweet & Elegant is exclusive to Blushed Crumbs
        if ($this->subdomain !== 'blushedcrumbs') {
            unset($all['sweet_elegant']);
        }

        return $all;
    }

    /**
     * The onboarding flow's own theme gating (starter-only unless pro),
     * layered on top of getAvailableThemesForTenant()'s sweet_elegant
     * exclusivity. Shared by DraftSynthesisService (Phase 5, picks the
     * enum Gemini is constrained to) and the review UI (Phase 8, so the
     * picker can't offer a theme synthesis was never allowed to choose)
     * — kept in one place rather than duplicated in both.
     */
    public function onboardingAvailableThemes(?string $selectedPlan = null): array
    {
        $all = $this->getAvailableThemesForTenant();

        if ($this->plan_tier === 'pro' || $selectedPlan === 'pro') {
            return $all;
        }

        return array_intersect_key($all, array_flip(array_keys(static::getStarterThemes())));
    }

    /**
     * Master theme registry — all themes across the platform.
     */
    public static function getAllThemes(): array
    {
        return [
            'sweet_elegant' => [
                'id' => 'sweet_elegant',
                'name' => '🌸 Sweet & Elegant',
                'subtitle' => 'Romantic pinks, luxury vintage script, soft cloud dividers',
                'preview_bg' => '#fcebf1',
                'preview_accent' => '#e67399',
                'exclusive' => true,
            ],
            'rustic_kitchen' => [
                'id' => 'rustic_kitchen',
                'name' => '🥖 Rustic Kitchen',
                'subtitle' => 'Warm terracotta, linen beige, artisanal bakery feel',
                'preview_bg' => '#f9f5f0',
                'preview_accent' => '#c86d51',
            ],
            'modern_bakery' => [
                'id' => 'modern_bakery',
                'name' => '✨ Modern Bakery',
                'subtitle' => 'Sleek dark/light minimalism, bold contemporary typography',
                'preview_bg' => '#f8fafc',
                'preview_accent' => '#1e293b',
            ],
            'playful_treats' => [
                'id' => 'playful_treats',
                'name' => '🧁 Playful Treats',
                'subtitle' => 'Vibrant pastels, cheerful cyan & coral energy',
                'preview_bg' => '#ecfeff',
                'preview_accent' => '#06b6d4',
            ],
            'country_farmhouse' => [
                'id' => 'country_farmhouse',
                'name' => '🥯 Artisan Deli',
                'subtitle' => 'Bold mustard & charcoal, full-bleed photography, deli-counter energy',
                'preview_bg' => '#f6efe1',
                'preview_accent' => '#c1810a',
            ],
            'artisan_sourdough' => [
                'id' => 'artisan_sourdough',
                'name' => '🌸 Petal & Crumb',
                'subtitle' => 'Blush mauve & cream, romantic serif, floating rounded cards',
                'preview_bg' => '#c9a08f',
                'preview_accent' => '#8b5a45',
            ],
            'clean_minimal' => [
                'id' => 'clean_minimal',
                'name' => '🌙 Midnight Bakehouse',
                'subtitle' => 'Deep navy & brushed gold, elegant serif, editorial luxury feel',
                'preview_bg' => '#f7f5f1',
                'preview_accent' => '#b8935a',
            ],
            'sunny_whisk' => [
                'id' => 'sunny_whisk',
                'name' => '☀️ Sunny Whisk',
                'subtitle' => 'Playful diagonal color blocks — sky blue, mustard yellow & bubblegum pink',
                'preview_bg' => '#bdeaf5',
                'preview_accent' => '#f5c518',
            ],
            'daily_batch' => [
                'id' => 'daily_batch',
                'name' => '🖤 The Daily Batch',
                'subtitle' => 'Bold mustard & jet black, circular photo frames, bakery-meets-agency energy',
                'preview_bg' => '#f2d310',
                'preview_accent' => '#14140f',
            ],
            'lavender_bloom' => [
                'id' => 'lavender_bloom',
                'name' => '💜 Lavender Bloom',
                'subtitle' => 'Orchid purple & soft lavender, boutique product tiles, craft-shop editorial energy',
                'preview_bg' => '#f6eefa',
                'preview_accent' => '#8e4fa3',
            ],
        ];
    }

    /**
     * Backward-compatible static accessor (for views that call Tenant::getAvailableThemes()).
     */
    public static function getAvailableThemes(): array
    {
        return static::getAllThemes();
    }

    public static function getDefaultFormSchema()
    {
        return [];
    }

    // ─── Relationships ───

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function galleries()
    {
        return $this->hasMany(GalleryItem::class);
    }

    public function emailSubscribers()
    {
        return $this->hasMany(EmailSubscriber::class);
    }

    public function emailCampaigns()
    {
        return $this->hasMany(EmailCampaign::class);
    }

    public function supportTickets()
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function onboardingDrafts()
    {
        return $this->hasMany(\App\Models\Onboarding\OnboardingDraft::class);
    }

    public function activeOnboardingDraft()
    {
        return $this->belongsTo(\App\Models\Onboarding\OnboardingDraft::class, 'active_onboarding_draft_id');
    }
}
