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
 * real businesses. Photos are placeholder stock images (loremflickr), not
 * AI-generated or scraped from a real bakery's real photos.
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
                    'onboarding_completed' => true,
                    'max_reviews_display' => 3,
                    'is_active' => true,
                    'is_demo' => true,
                ]
            );

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
            foreach ($b['gallery_tags'] as $gIndex => $tag) {
                $lock = ($i * 100) + $gIndex + 1;
                GalleryItem::create([
                    'tenant_id' => $tenant->id,
                    'title' => ucfirst(str_replace('-', ' ', $tag)),
                    'category' => $b['gallery_category'],
                    'image_url' => "https://loremflickr.com/800/600/{$tag}/all?lock={$lock}",
                    'alt_text' => "{$b['name']} — " . str_replace('-', ' ', $tag),
                    'sort_order' => $gIndex,
                    'is_hero' => $gIndex === 0,
                    'is_visible' => true,
                    'source' => 'demo_seed',
                ]);
            }
        }
    }

    private function siteContent(array $b): array
    {
        $defaults = Tenant::getDefaultSiteContent($b['name']);

        return array_merge($defaults, [
            'hero_subheading' => $b['tagline'],
            'hero_headline' => $b['name'],
            'hero_bg_url' => "https://loremflickr.com/1600/900/{$b['gallery_tags'][0]}/all?lock=" . crc32($b['slug'] . '-hero'),
            'promo_headline' => $b['promo_headline'],
            'promo_subtext' => $b['promo_subtext'],
            'promo_bg_image_url' => "https://loremflickr.com/1600/900/{$b['gallery_tags'][1]}/all?lock=" . crc32($b['slug'] . '-promo'),
            'whimsical_title' => $b['whimsical_title'],
            'whimsical_bullets' => $b['whimsical_bullets'],
            'whimsical_image_url' => "https://loremflickr.com/900/1100/{$b['gallery_tags'][2]}/all?lock=" . crc32($b['slug'] . '-whimsical'),
            'reviews' => array_map(fn ($r) => ['name' => $r[0], 'quote' => $r[1], 'stars' => 5], $b['reviews']),
            'cta_bg_image_url' => "https://loremflickr.com/1600/700/{$b['gallery_tags'][0]}/all?lock=" . crc32($b['slug'] . '-cta'),
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
                'gallery_tags' => ['caramel-cake', 'wedding-cake', 'hummingbird-cake', 'buttercream', 'pie', 'cupcakes'],
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
                'gallery_tags' => ['cookies', 'cookie-cake', 'sugar-cookies', 'cookie-box', 'chocolate-chip-cookie', 'bakery'],
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
                'gallery_tags' => ['sourdough', 'croissant', 'bread', 'pastry', 'bakery-shelf', 'galette'],
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
                'gallery_tags' => ['floral-cake', 'wedding-cake', 'cupcake-tower', 'mini-cake', 'pastel-cake', 'buttercream-flowers'],
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
                'gallery_tags' => ['modern-cake', 'geometric-cake', 'dessert-table', 'minimalist-cake', 'cake-design', 'event-cake'],
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
                'gallery_tags' => ['wedding-cake', 'sugar-flowers', 'tiered-cake', 'groom-cake', 'elegant-cake', 'wedding-reception'],
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
                'gallery_tags' => ['donuts', 'glazed-donut', 'pastry-case', 'bakery-counter', 'filled-donut', 'coffee-and-donuts'],
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
                'gallery_tags' => ['red-velvet-cake', 'drip-cake', 'birthday-cake', 'dessert-box', 'chocolate-cake', 'cake-topper'],
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
                'gallery_tags' => ['macarons', 'fruit-tart', 'entremet-cake', 'french-pastry', 'patisserie', 'mousse-cake'],
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
                'gallery_tags' => ['pie', 'cinnamon-rolls', 'bread-loaf', 'apple-pie', 'bakery-shop', 'pastry-case'],
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
