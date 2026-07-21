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
        'owner_name',
        'email',
        'phone',
        'plan_tier',
        'theme_id',
        'payment_settings',
        'form_schema',
        'site_content',
        'section_settings',
        'booking_settings',
        'is_active',
    ];

    protected $casts = [
        'payment_settings' => 'array',
        'form_schema' => 'array',
        'site_content' => 'array',
        'section_settings' => 'array',
        'booking_settings' => 'array',
        'is_active' => 'boolean',
    ];

    public static function getDefaultSiteContent()
    {
        return [
            'hero_subheading' => 'Order For Any Occasion',
            'hero_headline' => 'Blushed Crumbs Bakehouse',
            'hero_cta_primary' => 'Order Now',
            'hero_cta_secondary' => 'Our Flavors',
            'highlights' => [
                ['icon' => '🎂', 'title' => 'Easy Catering', 'desc' => 'Add custom baked goods to any occasion'],
                ['icon' => '🚚', 'title' => 'Freshly Baked', 'desc' => 'Made to order right before your event'],
                ['icon' => '📦', 'title' => 'Local Delivery', 'desc' => 'Flexible pickup & delivery options'],
                ['icon' => '💖', 'title' => 'Baked with Love', 'desc' => 'Cottage bakery crafted with care'],
            ],
            'promo_video_url' => 'images/download (2) (1).mp4',
            'promo_headline' => '$10 Off Your First Order!',
            'promo_subtext' => 'Follow us on social media or join our community for instant discounts.',
            'how_it_works' => [
                ['title' => 'Pick Your Date & Flavors', 'desc' => 'Use our 12-step form to choose your size, cake flavor, frosting, and upload your inspiration images.'],
                ['title' => 'Approve Design & Deposit', 'desc' => 'Receive your custom invoice & quote via email. Place a 50% deposit to lock in your date on our calendar.'],
                ['title' => 'Fresh Pickup or Delivery', 'desc' => 'We bake your creation fresh right before your event. Pick up at our kitchen or get venue delivery!'],
            ],
            'whimsical_title' => 'Whimsical Creations for Every Milestone',
            'whimsical_bullets' => [
                'Custom Wedding Cakes: Elegant, timeless, and tailored entirely to your love story.',
                'Birthday & Party Cakes: From whimsical children\'s themes to sleek, modern adult designs.',
                'Anniversary Cakes: Recommence your vows with a beautiful, nostalgic dessert.',
                'Signature Sheet Cakes: Perfect for larger crowds, school events, or casual get-togethers.',
                'Gourmet Chocolate-Covered Strawberries: Ripe, juicy berries hand-dipped in chocolate.'
            ],
            'reviews' => [
                ['name' => 'Kristen Ramirez', 'quote' => 'Absolutely breathtaking work!! The detail put into this cake was insane and it tasted unbelievable!! You made me look like the best sister ever, thank you so much for your talent and hard work!!', 'stars' => 5],
                ['name' => 'Lynne Escue', 'quote' => 'I ordered a strawberry smash cake for my 1 year old, with strawberries on top and custom icing and she devoured it ✨ The cake was so moist & icing wasn’t too sweet! Pick up process was super easy.', 'stars' => 5],
                ['name' => 'Alexis', 'quote' => 'Not only was I extremely shocked at how cute this cake was, I was truly SO surprised with how delicious it was! I tried to get a slice and having trouble cutting the back, I was SO CONFUSED.', 'stars' => 5],
                ['name' => 'Pamela Cortes', 'quote' => 'She was super friendly and easy to work with! The cake looked awesome, everything I was hoping for! ❤️', 'stars' => 5],
            ],
            'faqs' => [
                ['q' => '📅 How far in advance should I order?', 'a' => 'We require at least 3 days advance notice for custom orders. For weddings and large multi-tier events, we recommend booking 2-4 weeks in advance to reserve your date.'],
                ['q' => '💳 What is the deposit requirement?', 'a' => 'A 50% non-refundable deposit is required at booking to secure your date. Remaining balance is due prior to pickup or delivery.'],
                ['q' => '⚠️ Allergy Information', 'a' => 'We operate under Tennessee cottage food laws. Our kitchen processes wheat, eggs, dairy, and nuts. Please disclose all food allergies during checkout!'],
            ],
            'cta_banner_url' => 'images/34d48b27-1dd9-4784-8c8d-b378c3388060.mp4',
            'cta_headline' => 'Ready For Your Perfect Cake?',
            'cta_subtext' => 'Order your plan or custom order now',
            'cta_btn_text' => 'Order Now',
            'about_title' => 'About Our Bakery',
            'about_bio' => 'Welcome to our bakehouse! We specialize in custom artisanal cakes, gourmet treats, and unforgettable dessert experiences crafted with premium ingredients and passion.',
            'contact_hours' => 'Mon-Sat: 8:00 AM - 6:00 PM | Sun: Closed',
            'contact_location' => 'Nashville, TN & Surrounding Areas',
            'contact_instagram' => '@Blushed_Crumbs',
            'contact_facebook' => 'Blushed Crumbs',
        ];
    }

    public function getSiteContent($key, $default = null)
    {
        $content = $this->site_content ?? self::getDefaultSiteContent();
        $val = data_get($content, $key);
        if ($val !== null && $val !== '') {
            return $val;
        }
        return data_get(self::getDefaultSiteContent(), $key, $default);
    }

    public static function getDefaultSectionSettings()
    {
        return [
            'hero' => ['id' => 'hero', 'name' => '🌟 Hero Banner Section', 'enabled' => true, 'order' => 1],
            'highlights' => ['id' => 'highlights', 'name' => '🛡️ Trust Highlights Bar', 'enabled' => true, 'order' => 2],
            'promo_video' => ['id' => 'promo_video', 'name' => '🎥 Video Background Promo Banner', 'enabled' => true, 'order' => 3],
            'categories' => ['id' => 'categories', 'name' => '🧁 Category Showcase Grid', 'enabled' => true, 'order' => 4],
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

    public static function getAvailableThemes()
    {
        return [
            'sweet_elegant' => [
                'id' => 'sweet_elegant',
                'name' => '🌸 Sweet & Elegant',
                'subtitle' => 'Romantic pinks, luxury vintage script, soft cloud dividers',
                'preview_bg' => '#fcebf1',
                'preview_accent' => '#e67399',
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
        ];
    }

    public static function getDefaultFormSchema()
    {
        return [
            [
                'id' => 'step_1',
                'type' => 'products',
                'title' => 'Build Your Order',
                'subtext' => 'Select items from our product catalog below',
                'options' => '',
                'description' => ''
            ],
            [
                'id' => 'step_2',
                'type' => 'calendar',
                'title' => 'Select Your Date',
                'subtext' => 'Custom Order Booking',
                'options' => '',
                'description' => ''
            ],
            [
                'id' => 'step_3',
                'type' => 'flavors',
                'title' => 'Choose Your Flavors',
                'subtext' => 'Select all that apply (Luxury flavors +$10.00)',
                'options' => 'Strawberry Bliss, Vanilla Bean, Chocolate Dream, Creamy Hazelnut, Confetti Explosion, Red Velvet, Swirly Marble, Lemon Drop, Golden Carrot, Raspberry Swirl, Almond Elegance (LUX) (+$10.00), Pistachio ice (LUX) (+$10.00), Coconut Cream (LUX) (+$10.00), Cinnamon spice (LUX) (+$10.00), Cotton candy temptation (LUX) (+$10.00), Espresso Bean (LUX) (+$10.00), Cherry Bark (LUX) (+$10.00), Cookie Butter (LUX) (+$10.00), Tres Leches (LUX) (+$10.00), Banana Creme Pie (LUX) (+$10.00)',
                'description' => ''
            ],
            [
                'id' => 'step_4',
                'type' => 'frosting',
                'title' => 'Select Frosting',
                'subtext' => 'Select your preferred frosting type',
                'options' => 'Vanilla buttercream, Cream Cheese, Strawberry buttercream, Oreo buttercream, Chocolate buttercream, Confetti buttercream, Whip Cream',
                'description' => ''
            ],
            [
                'id' => 'step_5',
                'type' => 'fillings',
                'title' => 'Choice of Fillings?',
                'subtext' => 'Select all that apply',
                'options' => 'Fudge, Cookies and cream, Strawberries and cream, Peanut Butter, Nutella, Edible cookie dough, Lemon curd, Plain buttercream/frosting (no additional cost), Raspberry, Cheesecake, Dulce de Leche, Pineapple, Cream cheese, Banana',
                'description' => ''
            ],
            [
                'id' => 'step_6',
                'type' => 'textarea',
                'title' => 'Special Requests',
                'subtext' => 'Is there anything specific you want me to add or know about your order?',
                'options' => '',
                'description' => 'Type your notes here...'
            ],
            [
                'id' => 'step_7',
                'type' => 'fulfillment',
                'title' => 'Fulfillment Options',
                'subtext' => 'Select pickup or delivery and your preferred time frame',
                'options' => '8:30 AM, 9:00 AM, 9:30 AM, 10:00 AM, 10:30 AM',
                'description' => ''
            ],
            [
                'id' => 'step_8',
                'type' => 'allergies',
                'title' => 'Any Allergies?',
                'subtext' => 'By answering this question you understand any allergies not listed I will not be at fault for.',
                'options' => '',
                'description' => 'List any allergies here (or type "None")...'
            ],
            [
                'id' => 'step_9',
                'type' => 'social_discount',
                'title' => 'Social Media Follows',
                'subtext' => '$5 off for social media you follow or join! (Does not apply to ones you are already following)',
                'options' => 'Instagram: (@Blushed_Crumbs), Facebook group: (Blushed Crumbs), TikTok: (@Blushed_Crumbs), Facebook page: (Blushed Crumbs)',
                'description' => ''
            ],
            [
                'id' => 'step_10',
                'type' => 'file_upload',
                'title' => 'Inspiration Files',
                'subtext' => 'Have any pictures or designs you\'d like us to use for inspiration?',
                'options' => '',
                'description' => 'Supports PNG, JPG, JPEG'
            ],
            [
                'id' => 'step_11',
                'type' => 'terms',
                'title' => 'Terms & Conditions',
                'subtext' => 'PLEASE READ BEFORE ACCEPTING ‼️‼️‼️',
                'options' => '',
                'description' => ''
            ],
            [
                'id' => 'step_12',
                'type' => 'contact_info',
                'title' => 'Contact Information',
                'subtext' => 'Enter your details to finalize your order request',
                'options' => '',
                'description' => ''
            ],
        ];
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
