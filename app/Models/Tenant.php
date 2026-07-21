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
        'booking_settings',
        'is_active',
    ];

    protected $casts = [
        'payment_settings' => 'array',
        'form_schema' => 'array',
        'site_content' => 'array',
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
                ['icon' => '🍰', 'title' => 'Cake Tastings Available', 'desc' => 'Tasting boxes for wedding planning'],
                ['icon' => '🚗', 'title' => 'Delivery Available', 'desc' => 'To local venues and homes'],
                ['icon' => '📜', 'title' => 'Tennessee Licensed', 'desc' => 'Cottage food operation'],
            ],
            'whimsical_title' => 'Whimsical Creations for Every Milestone',
            'whimsical_bullets' => [
                'Custom Wedding Cakes: Elegant, timeless, and tailored entirely to your love story.',
                'Birthday & Party Cakes: From whimsical children\'s themes to sleek, modern adult designs.',
                'Anniversary Cakes: Recommence your vows with a beautiful, nostalgic dessert.',
                'Signature Sheet Cakes: Perfect for larger crowds, school events, or casual get-togethers.',
                'Gourmet Chocolate-Covered Strawberries: Ripe, juicy berries hand-dipped in chocolate.'
            ],
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
