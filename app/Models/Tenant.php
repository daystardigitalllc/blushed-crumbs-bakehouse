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
        'payment_settings',
        'form_schema',
        'booking_settings',
        'is_active',
    ];

    protected $casts = [
        'payment_settings' => 'array',
        'form_schema' => 'array',
        'booking_settings' => 'array',
        'is_active' => 'boolean',
    ];

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
                'subtext' => 'Select all that apply (Luxury flavors +$10)',
                'options' => 'Strawberry Bliss, Vanilla Bean, Chocolate Dream, Creamy Hazelnut, Confetti Explosion, Red Velvet, Swirly Marble, Lemon Drop, Golden Carrot, Raspberry Swirl, Almond Elegance (LUX), Pistachio ice (LUX), Coconut Cream (LUX), Cinnamon spice (LUX), Cotton candy temptation (LUX), Espresso Bean (LUX), Cherry Bark (LUX), Cookie Butter (LUX), Tres Leches (LUX), Banana Creme Pie (LUX)',
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
