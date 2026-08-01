<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Brand;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Product;
use App\Models\Review;
use App\Models\GalleryItem;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds ~10 fictional bakery tenants used purely as showcase/demo sites for
 * doughmain.pro's marketing (/examples page + sales conversations). Every
 * name, address, phone, and review below is invented — none of these are
 * real businesses. Gallery/hero photos are real, verified-on-topic stock
 * photos (curated Unsplash photo IDs, licensed for free use), not AI images
 * or anything scraped from a real bakery. Logos are generated locally as
 * simple SVG wordmarks — no photo of an object/person stands in for a logo.
 */
class DemoBakeriesSeeder extends Seeder
{
    public function run(): void
    {
        $brand = Brand::firstOrCreate(
            ['slug' => 'doughmain'],
            [
                'name' => 'DoughMain',
                'domain' => 'doughmain.pro',
                'branding_settings' => [
                    'tagline' => 'Create your bakery website with AI in minutes',
                    'primary_color' => '#e67399',
                    'secondary_color' => '#6d28d9',
                ],
                'is_active' => true,
            ]
        );

        $themes = Tenant::getAllThemes();

        foreach ($this->bakeries() as $i => $b) {
            $tenant = Tenant::updateOrCreate(
                ['slug' => $b['slug']],
                [
                    'brand_id' => $brand->id,
                    'name' => $b['name'],
                    'subdomain' => $b['slug'],
                    'owner_name' => $b['owner_name'],
                    'email' => "hello@{$b['slug']}.com",
                    'phone' => $b['phone'],
                    'city' => $b['city'],
                    'state' => $b['state'],
                    'country_code' => 'US',
                    'plan_tier' => 'pro',
                    'theme_id' => $b['theme'],
                    'instagram_url' => "https://instagram.com/{$b['slug']}",
                    'payment_settings' => [
                        'venmo' => '@' . $b['slug'],
                        'stripe_enabled' => false,
                    ],
                    'site_content' => $this->siteContent($b),
                    'form_schema' => $this->formSchema($b),
                    'onboarding_completed' => true,
                    'max_reviews_display' => 3,
                    'is_active' => true,
                    'is_demo' => true,
                ]
            );

            // Without this, the subdomain never resolves — ResolveTenant only
            // reads the domains table, it doesn't derive subdomains from the
            // brand's domain at request time (see AuthController::register()).
            $domainName = strtolower($b['slug'] . '.' . $brand->domain);
            if (!\Stancl\Tenancy\Database\Models\Domain::where('domain', $domainName)->exists()) {
                $tenant->domains()->create(['domain' => $domainName]);
            }

            $accent = $themes[$b['theme']]['preview_accent'] ?? '#e67399';
            $tenant->update(['logo_path' => $this->writeLogo($tenant->id, $b['name'], $accent)]);

            User::updateOrCreate(
                ['email' => "hello@{$b['slug']}.com"],
                [
                    'tenant_id' => $tenant->id,
                    'name' => $b['owner_name'],
                    'password' => Hash::make('Demo1234!'),
                    'role' => 'owner',
                ]
            );

            Product::where('tenant_id', $tenant->id)->delete();
            foreach ($b['products'] as $index => $prod) {
                Product::create([
                    'tenant_id' => $tenant->id,
                    'name' => $prod[0],
                    'price' => $prod[1],
                    'category' => $prod[2],
                    'is_active' => true,
                    'sort_order' => $index,
                ]);
            }

            Review::where('tenant_id', $tenant->id)->delete();
            foreach ($b['reviews'] as $rev) {
                Review::create([
                    'tenant_id' => $tenant->id,
                    'client_name' => $rev[0],
                    'rating' => 5,
                    'review_text' => $rev[1],
                    'is_featured' => true,
                ]);
            }

            GalleryItem::where('tenant_id', $tenant->id)->delete();
            foreach ($b['photos'] as $gIndex => $photo) {
                [$photoId, $label] = $photo;
                GalleryItem::create([
                    'tenant_id' => $tenant->id,
                    'title' => $label,
                    'category' => $b['gallery_category'],
                    'image_url' => $this->unsplashUrl($photoId, 800, 600),
                    'alt_text' => "{$b['name']} — {$label}",
                    'sort_order' => $gIndex,
                    'is_hero' => $gIndex === 0,
                    'is_visible' => true,
                    'source' => 'demo_seed',
                ]);
            }
        }
    }

    /**
     * Real Unsplash CDN image at a given crop size — every photo ID used
     * below was hand-verified (via Unsplash's own alt text) to actually
     * depict the food item it's assigned to, not a random tag match.
     */
    private function unsplashUrl(string $photoId, int $w, int $h): string
    {
        return "https://images.unsplash.com/{$photoId}?w={$w}&h={$h}&fit=crop&auto=format&q=80";
    }

    /**
     * A simple colored-circle SVG wordmark (bakery initials) written to this
     * tenant's uploads folder — same convention real tenants use
     * (public/uploads/tenants/{id}/logos/...). Avoids needing a photo (of an
     * object/person) to stand in for a logo, and avoids any font-file/GD
     * dependency since it's plain SVG text.
     */
    private function writeLogo(int $tenantId, string $name, string $accent): string
    {
        $slug = \Illuminate\Support\Str::slug($name);

        // Define custom styles and SVG paths based on the bakery slug
        $bg = '#fff7fa';
        $textColor = '#1a0a2e';
        $icon = '';
        $subtext = 'B A K E R Y';

        switch ($slug) {
            case 'sweet-magnolia-bakery':
                $bg = '#fff1f2';
                $textColor = '#4c0519';
                $subtext = 'S O U T H E R N   S T Y L E';
                $icon = '
                    <!-- Leaves -->
                    <path d="M90 85 C65 105 60 75 90 85 Z" fill="#10b981" opacity="0.7"/>
                    <path d="M90 85 C115 105 120 75 90 85 Z" fill="#10b981" opacity="0.7"/>
                    <!-- Flower Petals -->
                    <path d="M90 75 C75 52 105 52 90 75 Z" fill="#ec4899" opacity="0.85"/>
                    <path d="M90 75 C60 65 72 95 90 75 Z" fill="#f43f5e" opacity="0.85"/>
                    <path d="M90 75 C120 65 108 95 90 75 Z" fill="#f43f5e" opacity="0.85"/>
                    <path d="M90 75 C70 98 110 98 90 75 Z" fill="#ec4899" opacity="0.85"/>
                    <circle cx="90" cy="75" r="7" fill="#fef08a" stroke="#d97706" stroke-width="1"/>
                ';
                break;

            case 'the-cookie-cottage':
                $bg = '#fdf6e2';
                $textColor = '#431407';
                $subtext = 'F R E S H   C O O K I E S';
                $icon = '
                    <!-- Cottage walls -->
                    <rect x="72" y="82" width="36" height="28" fill="#faf6f0" stroke="#7c2d12" stroke-width="2"/>
                    <!-- Cookie roof -->
                    <path d="M55 82 A35 35 0 0 1 125 82 Z" fill="#d97706" stroke="#7c2d12" stroke-width="3"/>
                    <circle cx="70" cy="68" r="2.5" fill="#431407"/>
                    <circle cx="90" cy="60" r="3" fill="#431407"/>
                    <circle cx="110" cy="70" r="2.5" fill="#431407"/>
                    <!-- Cottage Door -->
                    <rect x="86" y="93" width="8" height="17" fill="#7c2d12" rx="1"/>
                ';
                break;

            case 'rustic-crumb-bakery':
                $bg = '#f5ebe0';
                $textColor = '#451a03';
                $subtext = 'A R T I S A N A L   B R E A D';
                $icon = '
                    <!-- Scored sourdough round loaf -->
                    <ellipse cx="90" cy="75" rx="34" ry="22" fill="#d97706" stroke="#78350f" stroke-width="3"/>
                    <path d="M70 70 Q90 64 110 70 M66 80 Q90 74 114 80 M74 60 Q90 54 106 60" fill="none" stroke="#f5ebe0" stroke-width="2.5" stroke-linecap="round"/>
                ';
                break;

            case 'honey-butter-cakes':
                $bg = '#fef3c7';
                $textColor = '#78350f';
                $subtext = 'S I G N A T U R E   C A K E S';
                $icon = '
                    <!-- Hexagon backdrop -->
                    <polygon points="90,42 105,51 105,69 90,78 75,69 75,51" fill="#fffbeb" stroke="#fbbf24" stroke-width="1.5"/>
                    <polygon points="110,75 125,84 125,102 110,111 95,102 95,84" fill="#fffbeb" stroke="#fbbf24" stroke-width="1.5"/>
                    <!-- Bee -->
                    <ellipse cx="90" cy="80" rx="12" ry="16" fill="#fbbf24" transform="rotate(30 90 80)"/>
                    <circle cx="100" cy="71" r="5" fill="#78350f"/>
                    <ellipse cx="80" cy="72" rx="7" ry="11" fill="#e0f2fe" opacity="0.8" transform="rotate(-20 80 72)"/>
                    <ellipse cx="92" cy="65" rx="6" ry="10" fill="#e0f2fe" opacity="0.8" transform="rotate(40 92 65)"/>
                    <path d="M82 82 L94 72 M85 88 L97 78" stroke="#78350f" stroke-width="3"/>
                ';
                break;

            case 'the-sugar-studio':
                $bg = '#0f172a';
                $textColor = '#f8fafc';
                $subtext = 'C U S T O M   B A K E S H O P';
                $icon = '
                    <!-- Bowl -->
                    <path d="M60 70 L120 70 C120 95 60 95 60 70 Z" fill="#06b6d4" stroke="#f8fafc" stroke-width="2.5" stroke-linecap="round"/>
                    <!-- Sparkles -->
                    <path d="M90 35 L93 42 L100 45 L93 48 L90 55 L87 48 L80 45 L87 42 Z" fill="#ec4899"/>
                    <path d="M112 48 L114 51 L118 52 L114 53 L112 56 L110 53 L106 52 L110 51 Z" fill="#22d3ee"/>
                ';
                break;

            case 'wildflower-wedding-cakes':
                $bg = '#faf5ff';
                $textColor = '#4c1d95';
                $subtext = 'L U X U R Y   D E S I G N S';
                $icon = '
                    <!-- Wedding cake -->
                    <rect x="76" y="88" width="28" height="16" fill="#ffffff" stroke="#4c1d95" stroke-width="1.5" rx="1"/>
                    <rect x="80" y="74" width="20" height="14" fill="#ffffff" stroke="#4c1d95" stroke-width="1.5" rx="1"/>
                    <rect x="84" y="62" width="12" height="12" fill="#ffffff" stroke="#4c1d95" stroke-width="1.5" rx="1"/>
                    <!-- Wildflowers wrapping -->
                    <path d="M72 100 Q65 72 78 62" fill="none" stroke="#059669" stroke-width="1" stroke-linecap="round"/>
                    <circle cx="78" cy="62" r="2.5" fill="#c084fc"/>
                    <path d="M108 100 Q112 82 102 70" fill="none" stroke="#059669" stroke-width="1" stroke-linecap="round"/>
                    <circle cx="102" cy="70" r="2.5" fill="#f472b6"/>
                ';
                break;

            case 'golden-whisk-bakehouse':
                $bg = '#1a0a2e';
                $textColor = '#fef08a';
                $subtext = 'P R E M I U M   P A S T R I E S';
                $icon = '
                    <!-- Balloon Whisk -->
                    <path d="M90 105 C75 75 75 40 90 40 C105 40 105 75 90 105 Z" fill="none" stroke="#fbbf24" stroke-width="2.5"/>
                    <path d="M90 105 C82 75 82 40 90 40 C98 40 98 75 90 105 Z" fill="none" stroke="#fbbf24" stroke-width="2"/>
                    <path d="M90 105 C90 75 90 40 90 40" fill="none" stroke="#fbbf24" stroke-width="1.5"/>
                    <rect x="85" y="105" width="10" height="8" fill="#d97706" rx="1"/>
                    <rect x="87" y="113" width="6" height="22" fill="#fbbf24" rx="2"/>
                    <!-- Whisk sparkles -->
                    <path d="M65 55 L67 59 L71 60 L67 61 L65 65 L63 61 L59 60 L63 59 Z" fill="#fef08a"/>
                    <path d="M115 50 L117 54 L121 55 L117 56 L115 60 L113 56 L109 55 L113 54 Z" fill="#fef08a"/>
                ';
                break;

            case 'velvet-vine-cakery':
                $bg = '#4c0519';
                $textColor = '#fbcfe8';
                $subtext = 'C U S T O M   C A K E S';
                $icon = '
                    <!-- Velvet cake layers -->
                    <rect x="68" y="85" width="44" height="22" fill="#831843" rx="2"/>
                    <rect x="77" y="67" width="26" height="18" fill="#831843" rx="2"/>
                    <!-- Vine wrapping -->
                    <path d="M62 98 Q78 108 90 90 T118 73" fill="none" stroke="#a3e635" stroke-width="2.5" stroke-linecap="round"/>
                    <path d="M118 73 C122 68 128 70 125 77 C120 81 116 76 118 73 Z" fill="#84cc16"/>
                ';
                break;

            case 'marigold-pastry-co':
                $bg = '#ffffff';
                $textColor = '#1a1a1a';
                $subtext = 'P A S T R Y   S H O P';
                $icon = '
                    <!-- Marigold Flower -->
                    <path d="M90 40 L97 58 L115 50 L102 65 L120 70 L102 75 L115 90 L97 82 L90 100 L83 82 L65 90 L78 75 L60 70 L78 65 L65 50 L83 58 Z" fill="#f97316"/>
                    <circle cx="90" cy="70" r="16" fill="#fbbf24" opacity="0.9"/>
                    <circle cx="90" cy="70" r="8" fill="#ea580c"/>
                ';
                break;

            case 'copper-kettle-bakery':
                $bg = '#fdfbf7';
                $textColor = '#3f2f25';
                $subtext = 'B A K E D   W I T H   L O V E';
                $icon = '
                    <!-- Copper kettle -->
                    <path d="M55 105 C55 75 125 75 125 105 Z" fill="#c2410c"/>
                    <path d="M65 77 C75 70 105 70 115 77" fill="none" stroke="#7c2d12" stroke-width="4" stroke-linecap="round"/>
                    <circle cx="90" cy="72" r="5" fill="#7c2d12"/>
                    <path d="M120 90 Q145 90 140 70" fill="none" stroke="#c2410c" stroke-width="6" stroke-linecap="round"/>
                    <path d="M70 75 C70 45 110 45 110 75" fill="none" stroke="#7c2d12" stroke-width="5" stroke-linecap="round"/>
                    <!-- Steam -->
                    <path d="M135 60 C138 52 132 48 135 40" fill="none" stroke="#a16207" stroke-width="2" stroke-linecap="round"/>
                    <path d="M142 58 C145 50 139 46 142 38" fill="none" stroke="#a16207" stroke-width="2" stroke-linecap="round"/>
                ';
                break;
        }

        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 180 180">
    <!-- Background Card -->
    <rect width="180" height="180" rx="28" fill="{$bg}"/>
    
    <!-- Vector Illustration Icon -->
    <g>
        {$icon}
    </g>
    
    <!-- Wordmark Typographical Layout -->
    <g>
        <text x="90" y="142" font-family="'Outfit', 'Inter', sans-serif" font-size="12" font-weight="700" fill="{$textColor}" text-anchor="middle" letter-spacing="0.02em">{$name}</text>
        <text x="90" y="156" font-family="'Inter', sans-serif" font-size="7.5" font-weight="600" fill="{$textColor}" opacity="0.6" text-anchor="middle" letter-spacing="0.12em">{$subtext}</text>
    </g>
</svg>
SVG;

        $dir = "uploads/tenants/{$tenantId}/logos";
        @mkdir(public_path($dir), 0755, true);
        $relativePath = "{$dir}/logo_v2.svg";
        file_put_contents(public_path($relativePath), $svg);

        return $relativePath;
    }

    private function siteContent(array $b): array
    {
        $defaults = Tenant::getDefaultSiteContent($b['name']);
        $photos = $b['photos'];

        return array_merge($defaults, [
            'hero_subheading' => $b['tagline'],
            'hero_headline' => $b['name'],
            'hero_bg_url' => $this->unsplashUrl($photos[0][0], 1600, 900),
            'promo_headline' => $b['promo_headline'],
            'promo_subtext' => $b['promo_subtext'],
            'promo_bg_image_url' => $this->unsplashUrl($photos[1][0], 1600, 900),
            'whimsical_title' => $b['whimsical_title'],
            'whimsical_bullets' => $b['whimsical_bullets'],
            'whimsical_image_url' => $this->unsplashUrl($photos[2][0], 900, 1100),
            'reviews' => array_map(fn ($r) => ['name' => $r[0], 'quote' => $r[1], 'stars' => 5], $b['reviews']),
            'cta_bg_image_url' => $this->unsplashUrl($photos[3][0], 1600, 700),
            'cta_headline' => $b['cta_headline'],
            'about_title' => 'About ' . $b['name'],
            'about_bio' => $b['about_bio'],
            'contact_hours' => $b['hours'],
            'contact_location' => "{$b['city']}, {$b['state']}",
            'contact_instagram' => "@{$b['slug']}",
            'seo_title' => "{$b['name']} | Custom Cakes & Bakery in {$b['city']}, {$b['state']}",
            'seo_description' => $b['about_bio'],
        ]);
    }

    /**
     * Order-form steps matching the shape AdminController::saveFormSchema()
     * persists and storefront/partials/order_modal.blade.php renders —
     * hand-written per bakery type since there's no AI onboarding pipeline
     * behind these demo tenants to generate one.
     */
    private function formSchema(array $b): array
    {
        $step = fn (string $title, string $type, string $options = '', string $subtext = '') => [
            'id' => 'step_' . uniqid(),
            'title' => $title,
            'subtext' => $subtext,
            'type' => $type,
            'options' => $options,
            'description' => '',
        ];

        return match ($b['order_flow']) {
            'cake' => [
                $step('Choose Your Size', 'products', '', 'Pick the item you\'d like to order'),
                $step('Pick Your Flavor', 'flavors', $b['flavor_options']),
                $step('Choose Your Frosting', 'frosting', 'Buttercream, Cream Cheese (+$5.00), Fondant (+$15.00)'),
                $step('Select Fillings', 'fillings', 'Fruit Filling (+$4.00), Chocolate Ganache (+$4.00), None'),
                $step('Pickup or Delivery', 'fulfillment'),
                $step('Upload Inspiration Photos', 'file_upload', '', 'Optional — share a photo of the style you want'),
                $step('Any Allergies We Should Know About?', 'allergies'),
                $step('Your Contact Information', 'contact_info'),
                $step('Deposit & Order Terms', 'terms'),
            ],
            'cookie' => [
                $step('Choose Your Item', 'products'),
                $step('Pick Your Flavors', 'flavors', $b['flavor_options']),
                $step('Add a Custom Message', 'textarea', '', 'Optional — for decorated cookies or cookie cakes'),
                $step('Pickup or Delivery', 'fulfillment'),
                $step('Upload Inspiration Photos', 'file_upload', '', 'Optional'),
                $step('Any Allergies We Should Know About?', 'allergies'),
                $step('Your Contact Information', 'contact_info'),
                $step('Deposit & Order Terms', 'terms'),
            ],
            'bakeshop' => [
                $step('Choose Your Item', 'products'),
                $step('Pick Your Flavors', 'flavors', $b['flavor_options']),
                $step('Pickup or Delivery', 'fulfillment'),
                $step('Any Allergies We Should Know About?', 'allergies'),
                $step('Your Contact Information', 'contact_info'),
                $step('Order Terms', 'terms'),
            ],
            'wedding' => [
                $step('Choose Your Package', 'products'),
                $step('Pick Your Flavors', 'flavors', $b['flavor_options']),
                $step('Choose Your Frosting', 'frosting', 'Buttercream, Fondant (+$15.00), Naked Cake (-$10.00)'),
                $step('Select Fillings', 'fillings', 'Fruit Filling (+$4.00), Chocolate Ganache (+$4.00), Lemon Curd (+$4.00)'),
                $step('Event Date & Fulfillment', 'fulfillment'),
                $step('Upload Inspiration Photos', 'file_upload', '', 'Share pictures of your venue, florals, or dress for design matching'),
                $step('Any Allergies We Should Know About?', 'allergies'),
                $step('Your Contact Information', 'contact_info'),
                $step('Deposit & Order Terms', 'terms'),
            ],
            default => [],
        };
    }

    private function bakeries(): array
    {
        return [
            [
                'slug' => 'sweetmagnolia',
                'name' => 'Sweet Magnolia Bakery',
                'owner_name' => 'Callie Foster',
                'city' => 'Nashville', 'state' => 'TN',
                'phone' => '+1 (615) 555-0142',
                'theme' => 'rustic_kitchen',
                'tagline' => 'Southern-Style Cakes & Pies, Baked Fresh Daily',
                'promo_headline' => 'Order Your Southern Celebration Cake',
                'promo_subtext' => 'From birthdays to church socials, we bake it fresh from our Nashville kitchen.',
                'whimsical_title' => 'Southern Charm, Baked In',
                'whimsical_bullets' => [
                    'Classic Caramel & Hummingbird Cakes made from family recipes.',
                    'Custom Wedding Cakes with a Southern garden aesthetic.',
                    'Fried Pies & Cobblers by the dozen for events.',
                    'Birthday Cakes with buttercream piping and fresh florals.',
                ],
                'cta_headline' => "Ready to Order Your Magnolia Cake?",
                'about_bio' => "Sweet Magnolia Bakery has been baking Southern-style cakes and pies out of our Nashville kitchen since day one. Every cake starts with a family recipe and finishes with hand-piped buttercream.",
                'hours' => 'Tue-Sat: 8:00 AM - 5:00 PM | Sun-Mon: Closed',
                'gallery_category' => 'Cakes',
                'order_flow' => 'cake',
                'flavor_options' => 'Caramel, Hummingbird (+$3.00), Red Velvet (+$3.00), Lemon, Chocolate',
                'photos' => [
                    ['photo-1558301211-0d8c8ddee6ec', 'Fondant celebration cake'],
                    ['photo-1623428454614-abaf00244e52', 'Wedding cake with fresh flowers'],
                    ['photo-1602351447937-745cb720612f', 'Chocolate cake with white icing'],
                    ['photo-1562007908-17c67e878c88', 'Fresh baked pie slice'],
                    ['photo-1519869325930-281384150729', 'Cupcakes on display'],
                    ['photo-1606890737304-57a1ca8a5b62', 'Chocolate cake with strawberry'],
                ],
                'products' => [
                    ['6" Caramel Cake', 68, 'Signature Cakes'],
                    ['8" Hummingbird Cake', 82, 'Signature Cakes'],
                    ['Chess Pie', 24, 'Pies'],
                    ['Fried Peach Pies (dozen)', 36, 'Pies'],
                    ['Dozen Cupcakes', 32, 'By The Dozen'],
                    ['Small 2-Tier Wedding Cake', 210, 'Wedding'],
                    ['Smash Cake', 32, 'Kids'],
                    ['Quarter Sheet Cake', 70, 'Sheet Cakes'],
                ],
                'reviews' => [
                    ['Marissa Todd', "The hummingbird cake tasted just like my grandmother's — I cried a little. Absolutely worth it."],
                    ['Dana Kirby', "Booked them for our wedding cake and it was the highlight of the reception. Beautiful and delicious."],
                    ['Ellen Price', "Chess pie was gone in one sitting at our family dinner. Ordering again for the holidays."],
                    ['Sam Whitfield', "Fast, friendly, and the caramel cake is unreal. Nashville's best kept secret."],
                ],
            ],
            [
                'slug' => 'cookiecottage',
                'name' => 'The Cookie Cottage',
                'owner_name' => 'Priya Nair',
                'city' => 'Franklin', 'state' => 'TN',
                'phone' => '+1 (615) 555-0198',
                'theme' => 'playful_treats',
                'tagline' => 'Giant Cookies, Cookie Cakes & Custom Cookie Boxes',
                'promo_headline' => 'Build Your Own Custom Cookie Box',
                'promo_subtext' => 'Mix and match flavors for parties, gifts, and everyday cravings.',
                'whimsical_title' => 'Cookies for Every Occasion',
                'whimsical_bullets' => [
                    'Giant Stuffed Cookies in 10+ rotating flavors.',
                    'Custom Cookie Cakes for birthdays and grad parties.',
                    'Decorated Sugar Cookie Sets for holidays and showers.',
                    'Gift Boxes shipped nationwide.',
                ],
                'cta_headline' => 'Craving a Custom Cookie Box?',
                'about_bio' => "The Cookie Cottage started as a Franklin farmers market stand and grew into a full cottage bakery specializing in giant stuffed cookies and custom cookie cakes.",
                'hours' => 'Mon-Sat: 9:00 AM - 6:00 PM | Sun: Closed',
                'gallery_category' => 'Cookies',
                'order_flow' => 'cookie',
                'flavor_options' => "Chocolate Chip, S'mores (+$1.00), Red Velvet (+$1.00), Birthday Cake, Peanut Butter",
                'photos' => [
                    ['photo-1633362218447-b80f27dc2ada', 'Fresh baked cookies'],
                    ['photo-1672351883507-212c1c70f9e9', 'Frosted cookie tray'],
                    ['photo-1734180206659-ad037b2024fe', 'Decorated sugar cookies'],
                    ['photo-1608069431017-9821bbbf0038', 'Heart-shaped cookies'],
                    ['photo-1480215529400-2995f91ddb96', 'Lining cookies on a baking sheet'],
                    ['photo-1703633294266-9a5992c3b37e', 'Assorted cookies'],
                ],
                'products' => [
                    ['Single Giant Cookie', 6, 'Cookies'],
                    ['Half Dozen Giant Cookies', 30, 'Cookies'],
                    ['Dozen Giant Cookies', 55, 'Cookies'],
                    ['Small Cookie Cake', 28, 'Cookie Cakes'],
                    ['Large Cookie Cake', 48, 'Cookie Cakes'],
                    ['Decorated Sugar Cookies (dozen)', 40, 'Decorated'],
                    ['Custom Gift Box', 45, 'Gift Boxes'],
                    ['Mini Cookie Party Pack', 65, 'Party Packs'],
                ],
                'reviews' => [
                    ['Jordan Lee', "Ordered a cookie cake for my son's birthday and it was a huge hit — better than any grocery store cake."],
                    ['Taylor Grant', "The gift box I sent to my sister arrived perfectly packed and she said it was the best cookie she's ever had."],
                    ['Nina Cole', "Their s'mores stuffed cookie should be illegal it's so good."],
                    ['Brian Ashe', "Super easy custom ordering process and they always nail the flavor combos I ask for."],
                ],
            ],
            [
                'slug' => 'rusticcrumb',
                'name' => 'Rustic Crumb Bakery',
                'owner_name' => 'Owen Marsh',
                'city' => 'Knoxville', 'state' => 'TN',
                'phone' => '+1 (865) 555-0177',
                'theme' => 'country_farmhouse',
                'tagline' => 'Artisan Sourdough, Pastries & Farmhouse Breads',
                'promo_headline' => 'Fresh Sourdough, Baked Every Morning',
                'promo_subtext' => 'Stop by or pre-order our small-batch farmhouse breads and pastries.',
                'whimsical_title' => 'Baked the Slow Way',
                'whimsical_bullets' => [
                    'Naturally Leavened Sourdough Loaves, baked daily.',
                    'Laminated Pastries — croissants, danishes, and morning buns.',
                    'Rustic Sandwich Breads for weekly subscriptions.',
                    'Seasonal Fruit Galettes made with local produce.',
                ],
                'cta_headline' => 'Reserve Your Loaf for the Week',
                'about_bio' => "Rustic Crumb Bakery bakes small-batch sourdough and farmhouse pastries in the heart of Knoxville, using slow fermentation and local flour.",
                'hours' => 'Wed-Sun: 7:00 AM - 2:00 PM (or until sold out) | Mon-Tue: Closed',
                'gallery_category' => 'Breads',
                'order_flow' => 'bakeshop',
                'flavor_options' => 'Classic Sourdough, Seeded Sourdough, Rosemary & Olive Oil (+$1.00), Cinnamon Raisin (+$1.00)',
                'photos' => [
                    ['photo-1613396874083-2d5fbe59ae79', 'Fresh baked sourdough loaf'],
                    ['photo-1590301157172-7ba48dd1c2b2', 'Bread loaves on table'],
                    ['photo-1623334044303-241021148842', 'Fresh baked croissants'],
                    ['photo-1530610476181-d83430b64dcd', 'Croissant on a tray'],
                    ['photo-1559811814-e2c57b5e69df', 'Sliced bread loaf'],
                    ['photo-1670819916757-e8d5935a6c65', 'Seasonal fruit galette'],
                ],
                'products' => [
                    ['Classic Sourdough Loaf', 9, 'Breads'],
                    ['Seeded Sourdough Loaf', 10, 'Breads'],
                    ['Butter Croissant', 5, 'Pastries'],
                    ['Almond Croissant', 6, 'Pastries'],
                    ['Morning Bun', 5, 'Pastries'],
                    ['Seasonal Fruit Galette', 22, 'Pastries'],
                    ['Weekly Bread Subscription (4 loaves)', 32, 'Subscriptions'],
                    ['Sandwich Loaf', 8, 'Breads'],
                ],
                'reviews' => [
                    ['Grace Holloway', "This is the real deal sourdough — crackly crust, chewy inside. I subscribe weekly now."],
                    ['Marcus Webb', "Almond croissants rival anything I've had in a real French bakery."],
                    ['Lily Chen', "They sell out fast for a reason. Get there early or pre-order."],
                    ['Trevor Banks', "Galette was incredible, not too sweet and packed with fruit."],
                ],
            ],
            [
                'slug' => 'honeybutter',
                'name' => 'Honey & Butter Cakes',
                'owner_name' => 'Renee Castillo',
                'city' => 'Murfreesboro', 'state' => 'TN',
                'phone' => '+1 (615) 555-0163',
                'theme' => 'artisan_sourdough',
                'tagline' => 'Custom Cakes & Cupcakes with a Delicate Touch',
                'promo_headline' => 'Custom Cakes for Your Next Celebration',
                'promo_subtext' => 'Hand-piped florals and delicate detail work for every order.',
                'whimsical_title' => 'Delicate Cakes, Made With Love',
                'whimsical_bullets' => [
                    'Custom Wedding & Bridal Shower Cakes with hand-piped florals.',
                    'Birthday Cakes in soft, romantic palettes.',
                    'Cupcake Towers for showers and receptions.',
                    'Mini Cakes for intimate celebrations.',
                ],
                'cta_headline' => 'Let\'s Design Your Cake',
                'about_bio' => "Honey & Butter Cakes crafts delicate, hand-piped custom cakes for weddings, showers, and birthdays from our Murfreesboro kitchen.",
                'hours' => 'Tue-Sat: 9:00 AM - 5:00 PM | Sun-Mon: Closed',
                'gallery_category' => 'Cakes',
                'order_flow' => 'cake',
                'flavor_options' => 'Vanilla Bean, Lemon (+$2.00), Strawberry (+$2.00), Funfetti, Chocolate',
                'photos' => [
                    ['photo-1525257831700-183b9b8bf5c4', 'Floral tiered fondant cake'],
                    ['photo-1535254973040-607b474cb50d', 'Layered fondant cake'],
                    ['photo-1621303837174-89787a7d4729', 'Pink and white celebration cake'],
                    ['photo-1599785209707-a456fc1337bb', 'Pink frosted cupcake'],
                    ['photo-1578922864601-79dcc7cbcea9', 'Pink cupcakes on a tray'],
                    ['photo-1595272568891-123402d0fb3b', 'Berry-topped white cake'],
                ],
                'products' => [
                    ['6" Custom Cake', 70, 'Custom Cakes'],
                    ['8" Custom Cake', 90, 'Custom Cakes'],
                    ['Mini Cake (4")', 38, 'Custom Cakes'],
                    ['Bridal Shower Cake', 150, 'Wedding'],
                    ['Small Wedding Cake (2-tier)', 240, 'Wedding'],
                    ['Cupcake Tower (4 dozen)', 130, 'Cupcakes'],
                    ['Dozen Cupcakes', 34, 'Cupcakes'],
                    ['Smash Cake', 34, 'Kids'],
                ],
                'reviews' => [
                    ['Ashley Monroe', "The buttercream florals on my bridal shower cake looked like real flowers. Stunning work."],
                    ['Katie Sanborn', "Every detail was perfect and it tasted even better than it looked."],
                    ['Devon Pierce', "Renee nailed the exact color palette I wanted for my daughter's cake."],
                    ['Morgan Reyes', "Best cupcake tower we've ever had at an event, hands down."],
                ],
            ],
            [
                'slug' => 'sugarstudioatx',
                'name' => 'The Sugar Studio',
                'owner_name' => 'Jenna Michaels',
                'city' => 'Austin', 'state' => 'TX',
                'phone' => '+1 (512) 555-0121',
                'theme' => 'modern_bakery',
                'tagline' => 'Modern Custom Cakes for the Bold & Bright',
                'promo_headline' => 'Sleek, Modern Cakes Made to Order',
                'promo_subtext' => 'Bold colors, clean lines, and next-level flavor — Austin\'s modern cake studio.',
                'whimsical_title' => 'Cake Design, Reimagined',
                'whimsical_bullets' => [
                    'Geometric & Abstract Cake Designs for modern celebrations.',
                    'Corporate Event Cakes with branded finishes.',
                    'Editorial-Style Dessert Tables for photo-ready events.',
                    'Bold Flavor Pairings beyond the classics.',
                ],
                'cta_headline' => 'Design Your Statement Cake',
                'about_bio' => "The Sugar Studio is Austin's modern custom cake studio, blending bold design with elevated flavor for weddings, brands, and milestone events.",
                'hours' => 'Mon-Fri: 10:00 AM - 6:00 PM | Sat: 10:00 AM - 2:00 PM | Sun: Closed',
                'gallery_category' => 'Cakes',
                'order_flow' => 'cake',
                'flavor_options' => 'Salted Caramel, Matcha (+$3.00), Passionfruit (+$3.00), Dark Chocolate, Vanilla Bean',
                'photos' => [
                    ['photo-1535141192574-5d4897c12636', 'Clean modern tiered cake'],
                    ['photo-1588195538326-c5b1e9f80a1b', 'Cake with chocolate drip'],
                    ['photo-1577998474517-7eeeed4e448a', 'Cake with sparkler'],
                    ['photo-1610670444950-0b29430891b4', 'Modern candle display'],
                    ['photo-1545696563-af8f6ec2295a', 'Iced celebration cake'],
                    ['photo-1602630209855-dceac223adfe', 'Sliced layered cake'],
                ],
                'products' => [
                    ['6" Modern Cake', 75, 'Custom Cakes'],
                    ['8" Modern Cake', 95, 'Custom Cakes'],
                    ['10" Statement Cake', 145, 'Custom Cakes'],
                    ['Corporate Branded Cake', 180, 'Corporate'],
                    ['Dessert Table Package (small)', 220, 'Dessert Tables'],
                    ['Dozen Designer Cupcakes', 42, 'Cupcakes'],
                    ['Cake Pops (dozen)', 36, 'Treats'],
                    ['Mini Cake Set (4)', 68, 'Custom Cakes'],
                ],
                'reviews' => [
                    ['Zoe Franklin', "Our company event cake matched our brand colors perfectly and tasted incredible."],
                    ['Chris Ibarra', "The dessert table they styled for our launch party stole the show."],
                    ['Paige Sutton', "Modern, clean, and delicious — exactly what we wanted for our engagement party."],
                    ['Anthony Ruiz', "Jenna's designs are on another level. Worth every penny."],
                ],
            ],
            [
                'slug' => 'wildflowerweddingcakes',
                'name' => 'Wildflower Wedding Cakes',
                'owner_name' => 'Harper Sinclair',
                'city' => 'Charleston', 'state' => 'SC',
                'phone' => '+1 (843) 555-0187',
                'theme' => 'clean_minimal',
                'tagline' => 'Timeless Wedding Cakes for Lowcountry Celebrations',
                'promo_headline' => 'Book Your Wedding Cake Consultation',
                'promo_subtext' => 'Elegant, timeless wedding cakes tailored to your love story.',
                'whimsical_title' => 'Elegant Cakes for Your Big Day',
                'whimsical_bullets' => [
                    'Multi-Tier Wedding Cakes with sugar floral detail.',
                    'Tasting Boxes to help you choose your flavors.',
                    'Groom\'s Cakes in any theme.',
                    'Anniversary & Vow Renewal Cakes.',
                ],
                'cta_headline' => 'Ready to Book Your Wedding Cake?',
                'about_bio' => "Wildflower Wedding Cakes has designed timeless wedding cakes for Charleston couples for years, pairing elegant sugar florals with rich, memorable flavor.",
                'hours' => 'By Appointment Only | Tue-Sat: 10:00 AM - 4:00 PM',
                'gallery_category' => 'Wedding',
                'order_flow' => 'wedding',
                'flavor_options' => 'Champagne, Almond (+$3.00), Lemon Elderflower (+$3.00), Vanilla Bean, Chocolate',
                'photos' => [
                    ['photo-1623428454614-abaf00244e52', 'Wedding cake with fresh florals'],
                    ['photo-1525257831700-183b9b8bf5c4', 'Tiered floral wedding cake'],
                    ['photo-1535254973040-607b474cb50d', 'Elegant fondant wedding cake'],
                    ['photo-1655762755958-cc0e10095c24', 'Cutting the wedding cake'],
                    ['photo-1568571780765-9276ac8b75a2', 'Sliced wedding cake'],
                    ['photo-1602630209855-dceac223adfe', 'Layered cake detail'],
                ],
                'products' => [
                    ['Wedding Cake Consultation', 0, 'Wedding'],
                    ['Wedding Tasting Box', 55, 'Wedding'],
                    ['Small 2-Tier Wedding Cake', 320, 'Wedding'],
                    ['Medium 3-Tier Wedding Cake', 480, 'Wedding'],
                    ['Large 4-Tier Wedding Cake', 650, 'Wedding'],
                    ['Groom\'s Cake', 165, 'Wedding'],
                    ['Sugar Flower Add-On (per flower)', 12, 'Add-Ons'],
                    ['Anniversary Cake', 95, 'Anniversary'],
                ],
                'reviews' => [
                    ['Caroline Deas', "Our wedding cake was more beautiful in person than we ever imagined. Worth every conversation we had leading up to it."],
                    ['Whitney Poole', "The tasting box made choosing flavors so easy and fun. Highly recommend booking early."],
                    ['Julia Marsh', "Harper listened to exactly what we wanted and delivered beyond expectations."],
                    ['Rebecca Simmons', "Best wedding vendor we worked with, period. The cake was a showstopper."],
                ],
            ],
            [
                'slug' => 'goldenwhisk',
                'name' => 'Golden Whisk Bakehouse',
                'owner_name' => 'Marcus Ellery',
                'city' => 'Denver', 'state' => 'CO',
                'phone' => '+1 (720) 555-0154',
                'theme' => 'sunny_whisk',
                'tagline' => 'Handcrafted Donuts, Pastries & Morning Treats',
                'promo_headline' => 'Fresh Donuts, Made Every Morning',
                'promo_subtext' => 'Rotating seasonal flavors, glazed fresh daily in the Mile High City.',
                'whimsical_title' => 'Mornings Made Sweeter',
                'whimsical_bullets' => [
                    'Hand-Glazed Donuts in rotating seasonal flavors.',
                    'Filled Pastries — Bavarian cream, fruit, and custard.',
                    'Donut Boxes for office and event catering.',
                    'Seasonal Specials for holidays year-round.',
                ],
                'cta_headline' => 'Order a Dozen for Tomorrow Morning',
                'about_bio' => "Golden Whisk Bakehouse hand-glazes fresh donuts and pastries every morning in Denver, with rotating seasonal flavors and catering boxes for any event.",
                'hours' => 'Daily: 6:00 AM - 1:00 PM (or until sold out)',
                'gallery_category' => 'Donuts',
                'order_flow' => 'bakeshop',
                'flavor_options' => 'Classic Glazed, Chocolate Frosted, Maple Bacon (+$1.00), Strawberry Sprinkle, Boston Cream (+$1.00)',
                'photos' => [
                    ['photo-1551024601-bec78aea704b', 'Glazed donut with toppings'],
                    ['photo-1646615077267-97c6088b74d9', 'Pink frosted donuts with sprinkles'],
                    ['photo-1618411640018-972400a01458', 'Assorted donuts on a plate'],
                    ['photo-1527515545081-5db817172677', 'Assorted flavor donuts'],
                    ['photo-1533910534207-90f31029a78e', 'Strawberry sprinkle donuts'],
                    ['photo-1551106652-a5bcf4b29ab6', 'Chocolate-coated donuts with sprinkles'],
                ],
                'products' => [
                    ['Single Donut', 4, 'Donuts'],
                    ['Half Dozen Donuts', 20, 'Donuts'],
                    ['Dozen Donuts', 36, 'Donuts'],
                    ['Filled Pastry', 5, 'Pastries'],
                    ['Office Catering Box (2 dozen)', 68, 'Catering'],
                    ['Seasonal Special Donut', 5, 'Seasonal'],
                    ['Donut Cake', 45, 'Specialty'],
                    ['Coffee & Donut Combo', 8, 'Combos'],
                ],
                'reviews' => [
                    ['Ryan Coats', "Best donuts in Denver, hands down. The maple bacon one is unreal."],
                    ['Sophie Trainor', "We order the catering box for our office every Friday now."],
                    ['Luis Ferrer', "Fresh, never greasy, always gone within the hour of opening."],
                    ['Amber Deleon', "Their seasonal pumpkin donut is the best fall treat in the city."],
                ],
            ],
            [
                'slug' => 'velvetandvine',
                'name' => 'Velvet & Vine Cakery',
                'owner_name' => 'Simone Actor',
                'city' => 'Savannah', 'state' => 'GA',
                'phone' => '+1 (912) 555-0139',
                'theme' => 'daily_batch',
                'tagline' => 'Bold, Beautiful Cakes with Southern Soul',
                'promo_headline' => 'Bold Cakes for Bold Celebrations',
                'promo_subtext' => 'Rich flavors and statement designs, baked fresh in Savannah.',
                'whimsical_title' => 'Cake with Character',
                'whimsical_bullets' => [
                    'Signature Red Velvet in every size.',
                    'Bold Drip Cakes with statement toppers.',
                    'Custom Birthday & Celebration Cakes.',
                    'Dessert Boxes for gifting and events.',
                ],
                'cta_headline' => 'Order Your Bold Cake Today',
                'about_bio' => "Velvet & Vine Cakery bakes bold, richly flavored cakes with statement designs out of our Savannah kitchen — Southern soul meets modern style.",
                'hours' => 'Tue-Sat: 9:00 AM - 6:00 PM | Sun-Mon: Closed',
                'gallery_category' => 'Cakes',
                'order_flow' => 'cake',
                'flavor_options' => 'Red Velvet, Chocolate Fudge (+$2.00), Caramel (+$2.00), Strawberry, Vanilla Bean',
                'photos' => [
                    ['photo-1714386148315-2f0e3eebcd5a', 'Slice of red velvet cake'],
                    ['photo-1714949134591-d6f2c581b20d', 'Red velvet cake with strawberries'],
                    ['photo-1685957652870-d56b0e5bea52', 'Red velvet cake slice'],
                    ['photo-1687877858381-e98c32796861', 'Layered cake topped with berries'],
                    ['photo-1659489397202-cd5d8875806b', 'Cakes with red frosting and florals'],
                    ['photo-1560172797-c656dbab1a39', 'Sliced red velvet cake'],
                ],
                'products' => [
                    ['6" Red Velvet Cake', 66, 'Signature Cakes'],
                    ['8" Red Velvet Cake', 84, 'Signature Cakes'],
                    ['Drip Cake (8")', 92, 'Specialty Cakes'],
                    ['Dozen Cupcakes', 34, 'Cupcakes'],
                    ['Dessert Box (6 pieces)', 38, 'Dessert Boxes'],
                    ['Quarter Sheet Cake', 72, 'Sheet Cakes'],
                    ['Small 2-Tier Cake', 200, 'Multi-Tier'],
                    ['Smash Cake', 34, 'Kids'],
                ],
                'reviews' => [
                    ['Destiny Rowe', "The red velvet cake is the best I've had in Savannah, hands down."],
                    ['Aaron Blake', "Ordered a drip cake for my birthday and it looked exactly like the inspiration pic."],
                    ['Chanel Douglas', "Dessert box makes the perfect gift, I order it every holiday season."],
                    ['Isaiah Grant', "Simone's cakes have serious personality. Always a hit at parties."],
                ],
            ],
            [
                'slug' => 'marigoldpastry',
                'name' => 'Marigold Pastry Co.',
                'owner_name' => 'Elise Beaumont',
                'city' => 'Portland', 'state' => 'OR',
                'phone' => '+1 (503) 555-0172',
                'theme' => 'lavender_bloom',
                'tagline' => 'French-Inspired Pastries & Custom Cakes',
                'promo_headline' => 'French Pastry, Portland Made',
                'promo_subtext' => 'Laminated dough, delicate cakes, and seasonal tarts crafted daily.',
                'whimsical_title' => 'A Little Slice of Paris',
                'whimsical_bullets' => [
                    'Macarons in rotating seasonal flavors.',
                    'Fruit Tarts with pastry cream and glazed fruit.',
                    'Entremet Cakes with layered mousse and glaze.',
                    'Custom Celebration Cakes, French-inspired.',
                ],
                'cta_headline' => 'Order Your French-Inspired Cake',
                'about_bio' => "Marigold Pastry Co. brings French pastry technique to Portland — macarons, fruit tarts, and entremet cakes made with precision and seasonal ingredients.",
                'hours' => 'Wed-Sun: 8:00 AM - 4:00 PM | Mon-Tue: Closed',
                'gallery_category' => 'Pastries',
                'order_flow' => 'bakeshop',
                'flavor_options' => 'Pistachio, Raspberry (+$1.00), Salted Caramel (+$1.00), Vanilla Bean, Chocolate',
                'photos' => [
                    ['photo-1569864358642-9d1684040f43', 'Assorted French macarons'],
                    ['photo-1558326567-98ae2405596b', 'Macarons on display'],
                    ['photo-1531594652722-292a43e752b4', 'Tray of French macarons'],
                    ['photo-1670819916757-e8d5935a6c65', 'Fresh fruit tarts'],
                    ['photo-1646321155195-44f0a4f91d1b', 'Berry-topped pastry'],
                    ['photo-1600477063726-b6b2473e43f9', 'Fruit-topped entremet cake'],
                ],
                'products' => [
                    ['Macarons (box of 6)', 15, 'Macarons'],
                    ['Macarons (box of 12)', 28, 'Macarons'],
                    ['Fruit Tart (individual)', 7, 'Tarts'],
                    ['Fruit Tart (8")', 42, 'Tarts'],
                    ['Entremet Cake (6")', 58, 'Entremet Cakes'],
                    ['Custom Celebration Cake (8")', 88, 'Custom Cakes'],
                    ['Croissant Box (6)', 24, 'Viennoiserie'],
                    ['Seasonal Tart Special', 45, 'Tarts'],
                ],
                'reviews' => [
                    ['Camille Fontaine', "These macarons taste like the ones I had in Paris. Absolutely perfect texture."],
                    ['Noah Bergstrom', "The entremet cake I ordered for my anniversary was a work of art and tasted incredible."],
                    ['Ingrid Solheim', "Fruit tarts are stunning and not overly sweet. My new go-to dessert order."],
                    ['Felix Dumont', "Elise's pastry skills are next level. Worth the drive across town."],
                ],
            ],
            [
                'slug' => 'copperkettle',
                'name' => 'Copper Kettle Bakery',
                'owner_name' => 'Wesley Hart',
                'city' => 'Boise', 'state' => 'ID',
                'phone' => '+1 (208) 555-0165',
                'theme' => 'rustic_kitchen',
                'tagline' => 'Homestyle Pies, Breads & Seasonal Treats',
                'promo_headline' => 'Order a Pie for Your Next Gathering',
                'promo_subtext' => 'Homestyle pies and breads baked in small batches, year-round.',
                'whimsical_title' => 'Homestyle Baking, Boise Made',
                'whimsical_bullets' => [
                    'Fruit & Cream Pies made from scratch.',
                    'Cinnamon Rolls baked fresh every morning.',
                    'Holiday Pie Pre-Orders for Thanksgiving & Christmas.',
                    'Rustic Loaves for weekly pickup.',
                ],
                'cta_headline' => 'Reserve Your Holiday Pie',
                'about_bio' => "Copper Kettle Bakery bakes homestyle pies, cinnamon rolls, and rustic breads from scratch in small batches out of our Boise kitchen.",
                'hours' => 'Mon-Sat: 7:00 AM - 4:00 PM | Sun: Closed',
                'gallery_category' => 'Pies',
                'order_flow' => 'bakeshop',
                'flavor_options' => 'Apple, Cherry (+$1.00), Pumpkin (Seasonal), Pecan (+$2.00)',
                'photos' => [
                    ['photo-1621743478914-cc8a86d7e7b5', 'Fresh baked apple pie'],
                    ['photo-1535920527002-b35e96722eb9', 'Apple pie on a plate'],
                    ['photo-1694632288834-17d86b340745', 'Pan of frosted cinnamon rolls'],
                    ['photo-1649308401368-a68b77116605', 'Plate of cinnamon rolls'],
                    ['photo-1620921592619-652411a0d01a', 'Bread loaf on a tray'],
                    ['photo-1638329261528-1932b0e63212', 'Fresh baked pie'],
                ],
                'products' => [
                    ['Apple Pie', 26, 'Pies'],
                    ['Cherry Pie', 28, 'Pies'],
                    ['Pumpkin Pie (seasonal)', 26, 'Pies'],
                    ['Cinnamon Rolls (half dozen)', 22, 'Pastries'],
                    ['Cinnamon Rolls (dozen)', 40, 'Pastries'],
                    ['Rustic Sandwich Loaf', 8, 'Breads'],
                    ['Holiday Pie Bundle (3 pies)', 72, 'Seasonal'],
                    ['Mini Pie (individual)', 7, 'Pies'],
                ],
                'reviews' => [
                    ['Heather Combs', "We order our Thanksgiving pies here every year without fail. Better than homemade."],
                    ['Gary Nolan', "Cinnamon rolls are the best in Boise, worth waking up early for."],
                    ['Patricia Wynn', "The apple pie tastes exactly like my mom's, which is the highest compliment I can give."],
                    ['Doug Ferris', "Reliable, delicious, and always fresh. Our go-to bakery for every holiday."],
                ],
            ],
        ];
    }
}
