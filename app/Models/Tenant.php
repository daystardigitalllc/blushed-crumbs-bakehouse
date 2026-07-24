<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'domain',
        'subdomain',
        'custom_domain',
        'brand_id',
        'owner_name',
        'email',
        'phone',
        'plan_tier',
        'theme_id',
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
        'max_reviews_display',
        'is_active',
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
        'is_active' => 'boolean',
    ];

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
            'promo_video_url' => 'images/download (2) (1).mp4',
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
            'cta_banner_url' => 'images/34d48b27-1dd9-4784-8c8d-b378c3388060.mp4',
            'cta_headline' => 'Ready For Your Custom Cake?',
            'cta_subtext' => 'Order your custom baking creation now',
            'cta_btn_text' => 'Order Now',
            'about_title' => 'About Our Bakery',
            'about_bio' => 'Welcome to ' . $name . '! We specialize in custom artisanal cakes, gourmet treats, and unforgettable dessert experiences crafted with premium ingredients and passion.',
            'contact_hours' => 'Mon-Sat: 8:00 AM - 6:00 PM | Sun: Closed',
            'contact_location' => 'Local Delivery & Pickup Available',
            'contact_instagram' => '',
            'contact_facebook' => '',
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
            'hero' => ['id' => 'hero', 'name' => '🌟 Hero Banner Section', 'enabled' => true, 'order' => 1],
            'highlights' => ['id' => 'highlights', 'name' => '🛡️ Trust Highlights Bar', 'enabled' => true, 'order' => 2],
            'promo_video' => ['id' => 'promo_video', 'name' => '🎥 Video Background Promo Banner', 'enabled' => true, 'order' => 3],
            'categories' => ['id' => 'categories', 'name' => '🧁 Category Showcase Grid', 'enabled' => false, 'order' => 4],
            'whimsical' => ['id' => 'whimsical', 'name' => '✨ Whimsical Creations & Specialties', 'enabled' => true, 'order' => 5],
            'how_it_works' => ['id' => 'how_it_works', 'name' => '📝 How Custom Ordering Works (3 Steps)', 'enabled' => true, 'order' => 6],
            'reviews' => ['id' => 'reviews', 'name' => '⭐ Customer Reviews & Social Proof', 'enabled' => true, 'order' => 7],
            'faq' => ['id' => 'faq', 'name' => '❓ FAQ & Bakery Policies', 'enabled' => true, 'order' => 8],
            'cta_banner' => ['id' => 'cta_banner', 'name' => '🎬 Footer Booking CTA Banner', 'enabled' => true, 'order' => 9],
        ];
    }

    public function getOrderedSections()
    {
        $sections = $this->section_settings ?? self::getDefaultSectionSettings();
        uasort($sections, function ($a, $b) {
            return ($a['order'] ?? 0) <=> ($b['order'] ?? 0);
        });
        return $sections;
    }

    /**
     * Get Starter (Free) themes available for onboarding.
     */
    public static function getStarterThemes(): array
    {
        $all = static::getAllThemes();
        $starterKeys = ['rustic_kitchen', 'modern_bakery', 'playful_treats'];
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
                'name' => '🪵 Rustic Kitchen',
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
                'name' => '🏡 Country Farmhouse',
                'subtitle' => 'Barn red & forest green, warm cream, Cracker Barrel charm',
                'preview_bg' => '#faf6f0',
                'preview_accent' => '#8B2500',
            ],
            'artisan_sourdough' => [
                'id' => 'artisan_sourdough',
                'name' => '🍞 Artisan Sourdough',
                'subtitle' => 'Golden wheat tones, earthy craft parchment warmth',
                'preview_bg' => '#fdf8f0',
                'preview_accent' => '#b8860b',
            ],
            'clean_minimal' => [
                'id' => 'clean_minimal',
                'name' => '◻️ Clean Minimal',
                'subtitle' => 'Ultra-clean Swiss minimalism, monochrome precision',
                'preview_bg' => '#ffffff',
                'preview_accent' => '#111111',
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

    public function supportTickets()
    {
        return $this->hasMany(SupportTicket::class);
    }
}
