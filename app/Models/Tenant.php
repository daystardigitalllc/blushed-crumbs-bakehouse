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
        'section_colors',
        'logo_path',
        'gallery_images',
        'gallery_categories',
        'instagram_url',
        'facebook_url',
        'payment_settings',
        'form_schema',
        'site_content',
        'section_settings',
        'booking_settings',
        'presale_settings',
        'calendar_configured_at',
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
        'section_colors' => 'array',
        'booking_settings' => 'array',
        'presale_settings' => 'array',
        'ai_generated_content' => 'array',
        'gallery_images' => 'array',
        'gallery_categories' => 'array',
        'onboarding_completed' => 'boolean',
        'onboarding_started_at' => 'datetime',
        'onboarding_completed_at' => 'datetime',
        'is_active' => 'boolean',
        'is_demo' => 'boolean',
        'calendar_configured_at' => 'datetime',
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
            'how_it_works_title',
            'how_it_works_subtitle',
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
            'cta_btn_action',
            'marquee_text',
            'featured_gallery_title',
            'featured_gallery_images',
            'about_title',
            'about_bio',
            'about_hero_title',
            'about_testimonial_quote',
            'about_testimonial_name',
            'about_testimonial_role',
            'about_ingredients_title',
            'about_ingredients',
            'about_specialties_title',
            'about_specialties',
            'menu_hero_subtitle',
            'menu_hero_title',
            'menu_hero_text',
            'menu_empty_title',
            'menu_empty_text',
            'gallery_hero_title',
            'gallery_hero_text',
            'gallery_empty_title',
            'gallery_empty_text',
            'policy_intro_text',
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
            'policy_product_singular',
            'policy_product_plural',
        ];
    }

    public static function getDefaultSiteContent(?string $bakeryName = null)
    {
        $name = $bakeryName ?? 'Artisanal Bakehouse';
        return [
            // Deliberately bakery-type-neutral — these are the literal fallback
            // whenever the AI onboarding pipeline skips an optional field (or
            // fails outright), so a cake-specific default here silently makes a
            // bread/cookie/pie bakery's site read as the wrong kind of business.
            // See DraftSynthesisService::mergeSiteContent().
            'hero_subheading' => 'Order For Any Occasion',
            'hero_headline' => $name,
            'hero_cta_primary' => 'Order Now',
            'hero_cta_secondary' => 'Our Treats',
            'highlights' => [
                ['icon' => '🧺', 'title' => 'Made to Order', 'desc' => 'Baked fresh for your order, not off a shelf'],
                ['icon' => '🚚', 'title' => 'Freshly Baked', 'desc' => 'Made to order right before pickup or delivery'],
                ['icon' => '📦', 'title' => 'Local Delivery', 'desc' => 'Flexible pickup & delivery options'],
                ['icon' => '💖', 'title' => 'Baked with Love', 'desc' => 'Small-batch bakery crafted with care'],
            ],
            'promo_video_url' => '',
            'promo_headline' => 'Special Bakery Orders!',
            'promo_subtext' => 'Order online directly from our kitchen for pickup or delivery.',
            'how_it_works' => [
                ['title' => 'Pick Your Date & Order', 'desc' => 'Use our order form to choose what you\'d like, share any special requests, and upload inspiration photos if you have them.'],
                ['title' => 'Approve & Deposit', 'desc' => 'Receive your custom invoice & quote via email. Place a deposit to lock in your date on our calendar.'],
                ['title' => 'Fresh Pickup or Delivery', 'desc' => 'We bake your order fresh right before it\'s due. Pick up at our kitchen or get delivery!'],
            ],
            'whimsical_title' => 'Handcrafted for Every Occasion',
            'whimsical_bullets' => [
                'Made to Order: Every item is baked fresh for you, not pulled off a shelf.',
                'Special Occasions: From birthdays to holidays, we help you celebrate right.',
                'Custom Requests: Have something specific in mind? We love bringing ideas to life.',
                'Quality Ingredients: Only the best goes into every batch we bake.',
                'Small-Batch Care: Handcrafted in small batches, never mass-produced.',
            ],
            // Deliberately empty — a fake testimonial is worse than none.
            // Themes hide the reviews section entirely when this (and any
            // real reviews) is empty rather than falling back to canned quotes.
            'reviews' => [],
            'faqs' => [
                ['q' => '📅 How far in advance should I order?', 'a' => 'We require at least 3 days advance notice for custom orders. For larger or multi-part orders, we recommend booking 2-4 weeks in advance.'],
                ['q' => '💳 What is the deposit requirement?', 'a' => 'A deposit is required at booking to secure your date. Remaining balance is due prior to pickup or delivery.'],
                ['q' => '⚠️ Allergy Information', 'a' => 'Please disclose all food allergies during checkout so we can accommodate your dietary needs!'],
            ],
            'cta_banner_url' => '',
            'cta_headline' => 'Ready to Order?',
            'cta_subtext' => 'Order your favorites now',
            'cta_btn_text' => 'Order Now',
            'cta_btn_action' => 'order',
            'marquee_text' => 'Baked Fresh Daily',
            'featured_gallery_title' => 'Featured Creations',
            'featured_gallery_images' => [],
            'about_title' => 'About Our Bakery',
            'about_bio' => 'Welcome to ' . $name . '! We specialize in handcrafted baked goods made fresh with premium ingredients and a whole lot of care.',
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

    /**
     * Per-theme CSS selectors for each Page Builder section's background,
     * heading, body text, and button -- the map a per-section color override
     * needs to know WHERE to inject a rule. Hand-audited per theme (section
     * markup is not consistent across themes; several are fully bespoke).
     *
     * 'bg_mode' controls how the background override is generated:
     *   - 'flat'    : background-color override only (safe -- preserves any
     *                 separate background-image texture/pattern on the same
     *                 element).
     *   - 'gradient': the section's default look is a gradient or photo set
     *                 via inline style in the blade file. A flat color
     *                 override still works (background !important beats an
     *                 inline non-important style) but replaces that
     *                 gradient/photo entirely rather than tinting it --
     *                 surfaced to the baker as a warning in the UI.
     *   - 'skip'    : background override intentionally not offered (e.g.
     *                 modern_bakery's glassmorphism sections -- painting
     *                 them an opaque color removes the blur effect that's
     *                 the whole visual identity of that section).
     *   - 'before'  : the section's real element has no background of its
     *                 own -- a `::before` pseudo-element paints it instead
     *                 (sunny_whisk's diagonal-clip band system). The override
     *                 targets `{selector}::before`, since no specificity on
     *                 the element's own background can paint over a
     *                 pseudo-element layered visually on top of it.
     *
     * 'button' is null wherever that section renders no button in that
     * theme. 'button2' is set only where a section renders a SECOND,
     * independently-colorable button (Hero's primary + secondary CTA in
     * every theme but clean_minimal). Every button/button2 selector is
     * scoped with that section's own wrapper class -- `.btn-primary` is a
     * shared class reused across many sections (and the global order
     * modal), so an unscoped selector here would recolor every button on
     * the page, not just this section's.
     */
    public static function sectionColorSelectorMap(): array
    {
        // Shared by the four themes with a copy-pasted skeleton for most
        // sections (sweet_elegant, rustic_kitchen, playful_treats,
        // country_farmhouse) -- defined once, then only the real
        // differences are overridden per theme below.
        $sharedSkeleton = [
            'highlights' => ['bg' => '.highlights-bar', 'bg_mode' => 'flat', 'heading' => '.highlight-item h4', 'text' => '.highlight-item p', 'button' => null, 'button2' => null],
            'whimsical' => ['bg' => '.whimsical-section', 'bg_mode' => 'flat', 'heading' => '.whimsical-col-right h2', 'text' => '.whimsical-bullet-list li', 'button' => null, 'button2' => null],
            'reviews' => ['bg' => '#reviews.reviews-section', 'bg_mode' => 'flat', 'heading' => '.section-title-script', 'text' => '.cloud-review-card p', 'button' => null, 'button2' => null],
            'faq' => ['bg' => '.faq-policies-section', 'bg_mode' => 'gradient', 'heading' => '.section-title-script', 'text' => '.faq-policies-section p', 'button' => null, 'button2' => null],
            'cta_banner' => ['bg' => '.cta-video-banner', 'bg_mode' => 'gradient', 'heading' => '.cta-video-banner .cta-content h2', 'text' => '.cta-video-banner .cta-content p', 'button' => '.cta-video-banner .btn-primary', 'button2' => null],
            'featured_gallery' => ['bg' => '.featured-gallery-section', 'bg_mode' => 'gradient', 'heading' => '.section-title-script', 'text' => '.gallery-card-info h4', 'button' => null, 'button2' => null],
            'how_it_works' => ['bg' => '.how-it-works-section', 'bg_mode' => 'gradient', 'heading' => '.section-title-script', 'text' => '.how-it-works-section p', 'button' => null, 'button2' => null],
        ];

        // Real per-theme hero wrapper class -- needed even where bg_mode is
        // 'skip' (background is inline), purely so the button selectors
        // below can be scoped to this section instead of hitting every
        // `.btn-primary` on the page.
        $heroWrapper = [
            'sweet_elegant' => '.hero-section',
            'rustic_kitchen' => '.rustic-hero-split',
            'playful_treats' => '.playful-hero',
            'country_farmhouse' => '.farmhouse-hero',
            'modern_bakery' => '.modern-hero',
            'artisan_sourdough' => '.petal-hero',
            'clean_minimal' => '.midnight-hero',
            'sunny_whisk' => '.sw-hero',
            'daily_batch' => '.db-hero',
            'lavender_bloom' => '.lb-hero',
            'cherry_bakeshop' => '.cb-hero',
            'sage_sourdough' => '.sg-hero',
        ];

        $map = [];

        foreach (['sweet_elegant', 'rustic_kitchen', 'playful_treats', 'country_farmhouse'] as $t) {
            $map[$t] = $sharedSkeleton;
            $hw = $heroWrapper[$t];
            $map[$t]['hero'] = ['bg' => null, 'bg_mode' => 'skip', 'heading' => 'h1', 'text' => '.subheading', 'button' => "{$hw} .btn-primary", 'button2' => "{$hw} .btn-secondary"];
            $map[$t]['about'] = ['bg' => '.about-teaser-section', 'bg_mode' => 'flat', 'heading' => '.about-teaser-section h2', 'text' => '.about-teaser-section p', 'button' => '.about-teaser-section .btn-primary', 'button2' => null];
            $map[$t]['categories'] = ['bg' => '#categories.categories-section', 'bg_mode' => 'gradient', 'heading' => '.section-title-script', 'text' => '#categories p', 'button' => null, 'button2' => null];
            $map[$t]['promo_video'] = ['bg' => '.video-promo-banner', 'bg_mode' => 'gradient', 'heading' => '.video-promo-banner h2', 'text' => '.video-promo-banner p', 'button' => '.video-promo-banner .btn-primary', 'button2' => null];
        }

        // farmhouse's "about" and "categories" are bespoke with real
        // classes of their own -- overrides the generic assignment above.
        $map['country_farmhouse']['about'] = ['bg' => '.farmhouse-about', 'bg_mode' => 'flat', 'heading' => '.farmhouse-about h2', 'text' => '.farmhouse-about-copy p', 'button' => '.farmhouse-about .btn-primary', 'button2' => null];
        $map['country_farmhouse']['categories'] = ['bg' => '.farmhouse-menu-index-list', 'bg_mode' => 'flat', 'heading' => 'h2', 'text' => '.farmhouse-menu-item p', 'button' => '.farmhouse-menu-index .btn', 'button2' => null];

        // rustic_kitchen and playful_treats paint .highlights-bar with a
        // `background: linear-gradient(...) !important` shorthand, not a
        // plain background-color -- 'flat' mode only overrides the color
        // component, so the original gradient IMAGE stays on top and the
        // override is invisible. Needs 'gradient' mode to clear the image.
        $map['rustic_kitchen']['highlights']['bg_mode'] = 'gradient';
        $map['playful_treats']['highlights']['bg_mode'] = 'gradient';

        $map['modern_bakery'] = [
            'hero' => ['bg' => null, 'bg_mode' => 'skip', 'heading' => 'h1', 'text' => null, 'button' => '.modern-hero .btn-primary', 'button2' => '.modern-hero .btn-secondary'],
            'about' => ['bg' => '.about-teaser-section', 'bg_mode' => 'flat', 'heading' => '.about-teaser-section h2', 'text' => '.about-teaser-section p', 'button' => '.about-teaser-section .btn-primary', 'button2' => null],
            'highlights' => ['bg' => '.highlights-bar', 'bg_mode' => 'gradient', 'heading' => '.highlight-item h4', 'text' => '.highlight-item p', 'button' => null, 'button2' => null],
            'promo_video' => ['bg' => '.modern-promo-teaser', 'bg_mode' => 'flat', 'heading' => '.modern-promo-teaser h2', 'text' => '.modern-promo-teaser p', 'button' => '.modern-promo-teaser .btn-primary', 'button2' => null],
            'categories' => ['bg' => null, 'bg_mode' => 'skip', 'heading' => '.section-title-script', 'text' => null, 'button' => '#categories .btn-primary', 'button2' => null],
            'whimsical' => ['bg' => '.whimsical-section', 'bg_mode' => 'gradient', 'heading' => '.modern-cakes-banner h2', 'text' => '.whimsical-bullet-list li', 'button' => '.whimsical-section .btn-primary', 'button2' => null],
            'how_it_works' => ['bg' => null, 'bg_mode' => 'skip', 'heading' => '.section-title-script', 'text' => null, 'button' => null, 'button2' => null],
            'reviews' => ['bg' => null, 'bg_mode' => 'skip', 'heading' => '.section-title-script', 'text' => '.cloud-review-card p', 'button' => null, 'button2' => null],
            'faq' => ['bg' => '.modern-orders-section', 'bg_mode' => 'flat', 'heading' => '.section-title-script', 'text' => '.modern-accordion-item p', 'button' => null, 'button2' => null],
            'cta_banner' => ['bg' => '.cta-video-banner', 'bg_mode' => 'gradient', 'heading' => '.cta-video-banner .cta-content h2', 'text' => '.cta-video-banner .cta-content p', 'button' => '.cta-video-banner .btn-primary', 'button2' => null],
            'featured_gallery' => ['bg' => '.featured-gallery-section', 'bg_mode' => 'gradient', 'heading' => '.section-title-script', 'text' => '.gallery-card-info h4', 'button' => null, 'button2' => null],
        ];

        $map['artisan_sourdough'] = [
            'hero' => ['bg' => '.petal-hero', 'bg_mode' => 'gradient', 'heading' => 'h1', 'text' => null, 'button' => '.petal-hero .btn-primary', 'button2' => '.petal-hero .btn-secondary'],
            'about' => ['bg' => '.petal-about', 'bg_mode' => 'flat', 'heading' => '.petal-about h2', 'text' => '.petal-about-copy p', 'button' => '.petal-about .btn-primary', 'button2' => null],
            'highlights' => ['bg' => '.petal-features-row', 'bg_mode' => 'flat', 'heading' => '.petal-feature-card h4', 'text' => '.petal-feature-card p', 'button' => null, 'button2' => null],
            'promo_video' => ['bg' => '.petal-promo', 'bg_mode' => 'gradient', 'heading' => '.petal-promo h2', 'text' => '.petal-promo p', 'button' => '.petal-promo .btn-primary', 'button2' => null],
            'categories' => ['bg' => '#categories.categories-section', 'bg_mode' => 'flat', 'heading' => '.section-title-script', 'text' => '.petal-pick-card p', 'button' => null, 'button2' => null],
            'whimsical' => ['bg' => '.whimsical-section', 'bg_mode' => 'flat', 'heading' => '.whimsical-col-right h2', 'text' => '.whimsical-bullet-list li', 'button' => null, 'button2' => null],
            'how_it_works' => ['bg' => null, 'bg_mode' => 'skip', 'heading' => '.section-title-script', 'text' => '.petal-step-row p', 'button' => null, 'button2' => null],
            'reviews' => ['bg' => '#reviews.reviews-section', 'bg_mode' => 'flat', 'heading' => '.section-title-script', 'text' => '.cloud-review-card p', 'button' => null, 'button2' => null],
            'faq' => ['bg' => null, 'bg_mode' => 'skip', 'heading' => '.section-title-script', 'text' => '.petal-accordion-item p', 'button' => null, 'button2' => null],
            'cta_banner' => ['bg' => '.cta-video-banner', 'bg_mode' => 'gradient', 'heading' => '.cta-video-banner .cta-content h2', 'text' => '.cta-video-banner .cta-content p', 'button' => '.cta-video-banner .btn-primary', 'button2' => null],
            'featured_gallery' => ['bg' => '.featured-gallery-section', 'bg_mode' => 'gradient', 'heading' => '.section-title-script', 'text' => '.gallery-card-info h4', 'button' => null, 'button2' => null],
        ];

        $map['clean_minimal'] = [
            'hero' => ['bg' => null, 'bg_mode' => 'skip', 'heading' => '.midnight-hero-copy h1', 'text' => '.midnight-hero-copy p', 'button' => '.midnight-hero-copy .btn-primary', 'button2' => null],
            'about' => ['bg' => '.about-teaser-section', 'bg_mode' => 'flat', 'heading' => '.about-teaser-section h2', 'text' => '.about-teaser-section p', 'button' => '.about-teaser-section .btn-primary', 'button2' => null],
            'highlights' => ['bg' => '.midnight-included', 'bg_mode' => 'skip', 'heading' => '.midnight-included-grid h4', 'text' => '.midnight-included-grid p', 'button' => null, 'button2' => null],
            'promo_video' => ['bg' => '.midnight-spotlight', 'bg_mode' => 'flat', 'heading' => '.midnight-spotlight h2', 'text' => null, 'button' => '.midnight-spotlight .btn-primary', 'button2' => null],
            'categories' => ['bg' => null, 'bg_mode' => 'skip', 'heading' => '.section-title-script', 'text' => null, 'button' => null, 'button2' => null],
            'whimsical' => ['bg' => '.midnight-editorial', 'bg_mode' => 'skip', 'heading' => '.midnight-editorial-content h2', 'text' => '.midnight-editorial-content p', 'button' => null, 'button2' => null],
            'how_it_works' => ['bg' => '.midnight-process-wrap', 'bg_mode' => 'flat', 'heading' => '.midnight-process-card .subheading', 'text' => '.midnight-process-step p', 'button' => null, 'button2' => null],
            'reviews' => ['bg' => '#reviews.reviews-section', 'bg_mode' => 'flat', 'heading' => '.section-title-script', 'text' => '.cloud-review-card p', 'button' => null, 'button2' => null],
            'faq' => ['bg' => null, 'bg_mode' => 'skip', 'heading' => 'h3', 'text' => '.midnight-feature-item p', 'button' => null, 'button2' => null],
            'cta_banner' => ['bg' => '.cta-video-banner', 'bg_mode' => 'gradient', 'heading' => '.cta-video-banner .cta-content h2', 'text' => '.cta-video-banner .cta-content p', 'button' => '.cta-video-banner .btn-primary', 'button2' => null],
            'featured_gallery' => ['bg' => '.featured-gallery-section', 'bg_mode' => 'gradient', 'heading' => '.section-title-script', 'text' => '.gallery-card-info h4', 'button' => null, 'button2' => null],
        ];

        foreach (['sunny_whisk' => 'sw', 'daily_batch' => 'db', 'lavender_bloom' => 'lb', 'cherry_bakeshop' => 'cb', 'sage_sourdough' => 'sg'] as $t => $p) {
            $hw = $heroWrapper[$t];
            $map[$t] = [
                'hero' => ['bg' => $t === 'sunny_whisk' ? '.sw-hero-band' : $hw, 'bg_mode' => 'flat', 'heading' => "h1", 'text' => null, 'button' => "{$hw} .btn-primary", 'button2' => "{$hw} .btn-secondary"],
                'about' => ['bg' => ".{$p}-about-row", 'bg_mode' => 'skip', 'heading' => 'h2', 'text' => ".{$p}-about-copy p, .{$p}-about-copy-card p", 'button' => null, 'button2' => null],
                'highlights' => ['bg' => ".{$p}-about-row, .sw-process", 'bg_mode' => 'skip', 'heading' => 'h4', 'text' => ".{$p}-highlight-list li, .sw-process-row p", 'button' => $t === 'sunny_whisk' ? '.sw-process-row .btn-primary' : null, 'button2' => null],
                'promo_video' => ['bg' => ".{$p}-promo-banner, .video-promo-banner", 'bg_mode' => 'gradient', 'heading' => ".{$p}-promo-banner h2, .video-promo-banner h2", 'text' => ".{$p}-promo-banner p, .video-promo-banner p", 'button' => ".{$p}-promo-banner .btn-primary, .video-promo-banner .btn-primary", 'button2' => null],
                'categories' => ['bg' => "#categories", 'bg_mode' => 'flat', 'heading' => ".{$p}-section-title", 'text' => ".{$p}-shelf-card p", 'button' => null, 'button2' => null],
                'whimsical' => ['bg' => '.whimsical-section', 'bg_mode' => 'flat', 'heading' => ".{$p}-whimsical h2", 'text' => '.whimsical-bullet-list li', 'button' => null, 'button2' => null],
                'how_it_works' => ['bg' => '.how-it-works-section', 'bg_mode' => 'flat', 'heading' => ".{$p}-section-title", 'text' => ".{$p}-step-copy p", 'button' => null, 'button2' => null],
                'reviews' => ['bg' => '#reviews', 'bg_mode' => 'flat', 'heading' => ".{$p}-section-title", 'text' => ".{$p}-review-card p", 'button' => null, 'button2' => null],
                'faq' => ['bg' => '.faq-policies-section', 'bg_mode' => 'flat', 'heading' => ".{$p}-section-title", 'text' => ".{$p}-faq-card p, .{$p}-accordion-item p", 'button' => null, 'button2' => null],
                'cta_banner' => ['bg' => '.cta-video-banner', 'bg_mode' => 'gradient', 'heading' => '.cta-video-banner .cta-content h2', 'text' => '.cta-video-banner .cta-content p', 'button' => '.cta-video-banner .btn-primary', 'button2' => null],
                'featured_gallery' => ['bg' => '.featured-gallery-section', 'bg_mode' => 'flat', 'heading' => ".{$p}-section-title", 'text' => '.gallery-card-info h4', 'button' => null, 'button2' => null],
            ];
        }

        // sunny_whisk is the only theme in the $p-family loop whose section
        // backgrounds are painted entirely by a `.sw-band-*::before` pseudo
        // layer (clipped diagonal band) rather than the element itself --
        // daily_batch/lavender_bloom/cherry_bakeshop/sage_sourdough set
        // background directly on the real element, so they're unaffected.
        // 'before' mode targets the pseudo-element instead of the section,
        // since no amount of specificity on the section's own background
        // can paint over a pseudo-element layered on top of it.
        foreach (['categories', 'reviews', 'faq', 'featured_gallery', 'how_it_works'] as $__swSec) {
            $map['sunny_whisk'][$__swSec]['bg_mode'] = 'before';
        }

        // lavender_bloom's hero is a multi-stop `linear-gradient(...)` (an
        // actual image layer), not a solid color -- 'flat' mode only
        // touches background-color, leaving the gradient visible on top.
        $map['lavender_bloom']['hero']['bg_mode'] = 'gradient';

        return $map;
    }

    /**
     * The specific selector map for THIS tenant's active theme, falling back
     * to sweet_elegant's map for any theme not covered above (shouldn't
     * happen in practice -- every real theme_id is covered).
     */
    public function sectionColorSelectors(): array
    {
        $map = self::sectionColorSelectorMap();
        return $map[$this->theme_id] ?? $map['sweet_elegant'];
    }

    /**
     * Best-effort "what does this look like today" swatch values, used only
     * to pre-fill a section's color pickers before a baker has overridden
     * anything -- reads the theme's own palette variables rather than trying
     * to parse the exact computed color of every one of the ~50 distinct
     * per-section selectors (many of which are inline gradients with no
     * single resolvable color). Close enough for a starting swatch; it's
     * cosmetic only since the picker stays disabled until explicitly enabled.
     */
    public function themePaletteDefaults(): array
    {
        $fallback = ['bg' => '#ffffff', 'heading' => '#2c2419', 'text' => '#2c2419', 'button_bg' => '#c94b75', 'button_text' => '#ffffff'];

        $css = @file_get_contents(public_path($this->themeCssPath()));
        if (!$css || !preg_match('/body\.theme-' . preg_quote($this->theme_id, '/') . '\s*\{([^}]*)\}/s', $css, $block)) {
            return $fallback;
        }

        $vars = $fallback;
        if (preg_match('/--theme-card-bg:\s*(#[0-9a-fA-F]{3,8})/', $block[1], $m)) {
            $vars['bg'] = $m[1];
        }
        if (preg_match('/--dark-text:\s*(#[0-9a-fA-F]{3,8})/', $block[1], $m)) {
            $vars['heading'] = $m[1];
            $vars['text'] = $m[1];
        }
        if (preg_match('/--primary:\s*(#[0-9a-fA-F]{3,8})/', $block[1], $m)) {
            $vars['button_bg'] = $m[1];
        }
        if (preg_match('/\.btn-primary[^{]*\{[^}]*color:\s*(#[0-9a-fA-F]{3,8})/s', $css, $m)) {
            $vars['button_text'] = $m[1];
        }

        return $vars;
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
        $starterKeys = ['rustic_kitchen', 'modern_bakery', 'country_farmhouse', 'sage_sourdough', 'cherry_bakeshop'];
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
            'sage_sourdough' => [
                'id' => 'sage_sourdough',
                'name' => '🌿 Sage & Sourdough',
                'subtitle' => 'Deep sage green & warm cream, arch-window photo frames, farm-to-table warmth',
                'preview_bg' => '#f4f1e8',
                'preview_accent' => '#4a6350',
            ],
            'cherry_bakeshop' => [
                'id' => 'cherry_bakeshop',
                'name' => '🍒 Cherry Bakeshop',
                'subtitle' => 'Retro cherry red & cream, scalloped edges, vintage bakeshop charm',
                'preview_bg' => '#fff5f0',
                'preview_accent' => '#a0293f',
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

    /**
     * Flattens payment_settings (a mix of legacy flat strings like
     * ['venmo' => '@handle'] and custom entries like
     * ['custom_abc123' => ['name' => 'Apple Pay', 'handle' => '...', 'instructions' => '...']])
     * into one consistent list, so the dashboard, invoice email, and
     * customer-facing invoice page all render the same data the same way.
     */
    public function normalizedPaymentMethods(): array
    {
        $raw = $this->payment_settings ?? [];
        $out = [];

        if (is_array($raw)) {
            foreach ($raw as $key => $val) {
                if (is_array($val)) {
                    $handle = $val['handle'] ?? ($val['username'] ?? '');
                    if (trim((string) $handle) === '') {
                        continue;
                    }
                    $out[] = [
                        'key' => $key,
                        'name' => $val['name'] ?? ucfirst($key),
                        'handle' => $handle,
                        'instructions' => $val['instructions'] ?? null,
                    ];
                } elseif (is_string($val) && trim($val) !== '') {
                    $out[] = [
                        'key' => $key,
                        'name' => ucfirst($key),
                        'handle' => $val,
                        'instructions' => null,
                    ];
                }
            }
        }

        return $out;
    }

    /**
     * The baker's presale (predesigned-menu instant checkout) config, merged
     * over defaults so every reader gets a complete shape regardless of what
     * has actually been saved yet.
     */
    public function normalizedPresaleSettings(): array
    {
        $defaults = [
            'enabled' => false,
            'title' => 'Holiday Presale',
            'subtitle' => '',
            'pickup_start_date' => null,
            'pickup_end_date' => null,
            'tax_rate' => 0,
            'delivery_enabled' => false,
            'delivery_fee' => 0,
        ];

        $raw = is_array($this->presale_settings) ? $this->presale_settings : [];

        return array_merge($defaults, $raw);
    }

    /**
     * The baker's editable list of gallery categories. Falls back to the
     * originally-hardcoded set for any tenant created before this existed.
     */
    public function galleryCategories(): array
    {
        $categories = $this->gallery_categories;

        // Only a genuinely unconfigured (null) tenant falls back to the
        // starter set - an intentionally emptied array stays empty rather
        // than springing back to defaults every time this is read.
        if (!is_array($categories)) {
            return ['Cakes', 'Cupcakes', 'Treats', 'Weddings'];
        }

        return array_values($categories);
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
