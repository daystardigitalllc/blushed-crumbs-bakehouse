<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;
use App\Models\Product;
use App\Models\Review;
use App\Models\GalleryItem;
use App\Models\Order;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Tenant #1: Blushed Crumbs Bakehouse
        $tenant = Tenant::create([
            'name' => 'Blushed Crumbs Bakehouse',
            'slug' => 'blushedcrumbs',
            'domain' => 'blushedcrumbsbakehouse.com',
            'owner_name' => 'Blushed Crumbs Team',
            'email' => 'orders@blushedcrumbsbakehouse.com',
            'phone' => '+1 (555) 382-9901',
            'plan_tier' => 'pro', // Pro Tier ($50/mo)
            'payment_settings' => [
                'venmo' => '@Blushed_Crumbs',
                'cashapp' => '$BlushedCrumbs',
                'paypal' => 'https://paypal.me/BlushedCrumbs',
                'zelle' => 'orders@blushedcrumbsbakehouse.com',
                'stripe_enabled' => true,
            ],
            'is_active' => true,
        ]);

        // 2. Seed Products (matching the exact order builder list)
        $products = [
            ['name' => '4” Cake', 'price' => 45.00, 'category' => 'Single Tier'],
            ['name' => '6” Cake', 'price' => 65.00, 'category' => 'Single Tier'],
            ['name' => '7” Cake', 'price' => 75.00, 'category' => 'Single Tier'],
            ['name' => '8” Cake', 'price' => 85.00, 'category' => 'Single Tier'],
            ['name' => '9” Cake', 'price' => 95.00, 'category' => 'Single Tier'],
            ['name' => '10” Cake', 'price' => 115.00, 'category' => 'Single Tier'],
            ['name' => 'Bento Box', 'price' => 45.00, 'category' => 'Single Tier'],
            ['name' => 'Smash Cake', 'price' => 35.00, 'category' => 'Single Tier'],
            ['name' => 'Quarter-Sheet Cake', 'price' => 75.00, 'category' => 'Single Tier'],
            ['name' => 'Baddie On a Budget 🎀', 'price' => 45.00, 'category' => 'Single Tier'],
            ['name' => '1/2 Dozen Cupcakes', 'price' => 18.00, 'category' => 'By The Dozen'],
            ['name' => 'Dozen Cupcakes', 'price' => 35.00, 'category' => 'By The Dozen'],
            ['name' => 'Dozen Cake Shooters', 'price' => 50.00, 'category' => 'By The Dozen'],
            ['name' => 'Dozen Chocolate Covered Strawberries', 'price' => 30.00, 'category' => 'By The Dozen'],
            ['name' => 'Dozen Chocolate Covered Oreo', 'price' => 35.00, 'category' => 'By The Dozen'],
            ['name' => 'Dozen Chocolate Covered Marshmallows', 'price' => 20.00, 'category' => 'By The Dozen'],
            ['name' => 'Cakesickles', 'price' => 40.00, 'category' => 'Treats'],
            ['name' => 'Chocolate Rice Krispies', 'price' => 35.00, 'category' => 'Treats'],
            ['name' => 'Small Party Pack', 'price' => 100.00, 'category' => 'Party Packs'],
            ['name' => 'Medium Party Pack', 'price' => 165.00, 'category' => 'Party Packs'],
            ['name' => 'Large Party Pack', 'price' => 220.00, 'category' => 'Party Packs'],
            ['name' => 'Extra Large Party Pack', 'price' => 335.00, 'category' => 'Party Packs'],
            ['name' => 'Small 2 Tiered Cake', 'price' => 120.00, 'category' => 'Multi-Tier'],
            ['name' => '2 Tiered Heart Cake', 'price' => 145.00, 'category' => 'Multi-Tier'],
            ['name' => 'Medium 2 Tiered Cake', 'price' => 175.00, 'category' => 'Multi-Tier'],
            ['name' => 'Large 2 Tiered Cake', 'price' => 245.00, 'category' => 'Multi-Tier'],
            ['name' => 'Small 3 Tiered Cake', 'price' => 325.00, 'category' => 'Multi-Tier'],
            ['name' => 'Wedding Consultation', 'price' => 0.00, 'category' => 'Wedding'],
            ['name' => 'Wedding Tasting Pack', 'price' => 50.00, 'category' => 'Wedding'],
        ];

        foreach ($products as $index => $prod) {
            Product::create([
                'tenant_id' => $tenant->id,
                'name' => $prod['name'],
                'price' => $prod['price'],
                'category' => $prod['category'],
                'is_active' => true,
                'sort_order' => $index,
            ]);
        }

        // 3. Seed Reviews (matching client reviews from original site screenshot!)
        $reviews = [
            [
                'client_name' => 'Kristen Ramirez',
                'rating' => 5,
                'review_text' => 'Absolutely breathtaking work!! The detail put into this cake was insane and it tasted unbelievable!! You made me look like the best sister ever, thank you so much for your talent and hard work!!',
                'is_featured' => true,
            ],
            [
                'client_name' => 'Lynne Escue',
                'rating' => 5,
                'review_text' => 'I ordered a strawberry smash cake for my 1 year old, with strawberries on top and custom icing and she devoured it ✨ The cake was so moist & icing wasn’t too sweet! Pick up process was super easy. Very professional and communication was great.',
                'is_featured' => true,
            ],
            [
                'client_name' => 'Alexis',
                'rating' => 5,
                'review_text' => 'Not only was I extremely shocked at how cute this cake was, I was truly SO surprised with how delicious it was! I tried to get a slice and having trouble cutting the back, I was SO CONFUSED. I then realized my sister had secretly snuck one piece and went back for the rest of the cake for her second slice! I recommend to my friends all day long!',
                'is_featured' => true,
            ],
            [
                'client_name' => 'Pamela Cortes',
                'rating' => 5,
                'review_text' => 'She was super friendly and easy to work with! The cake looked awesome, everything I was hoping for! ❤️',
                'is_featured' => true,
            ],
        ];

        foreach ($reviews as $rev) {
            Review::create([
                'tenant_id' => $tenant->id,
                'client_name' => $rev['client_name'],
                'rating' => $rev['rating'],
                'review_text' => $rev['review_text'],
                'is_featured' => $rev['is_featured'],
            ]);
        }

        // 4. Gallery items (Empty by default — populated via Device Gallery Uploader in Admin)

        // 5. Seed Sample Urgent Custom Orders (Sorted by Due Date)
        Order::create([
            'tenant_id' => $tenant->id,
            'order_number' => 'BC-1092',
            'client_name' => 'Sarah Jenkins',
            'client_email' => 'sarah.j@example.com',
            'client_phone' => '(555) 234-8890',
            'due_date' => now()->addDay()->format('Y-m-d'), // Due TOMORROW!
            'time_slot' => '9:30 AM',
            'fulfillment_type' => 'pickup',
            'items' => [['name' => '6” Cake', 'price' => 65.00]],
            'flavors' => ['Strawberry Bliss'],
            'frosting' => ['Vanilla buttercream'],
            'fillings' => ['Strawberries and cream'],
            'special_notes' => 'Please add "Happy 30th Birthday Emma!" in gold lettering',
            'allergies' => 'None',
            'subtotal' => 65.00,
            'discount_amount' => 5.00,
            'total_price' => 60.00,
            'deposit_amount' => 30.00,
            'deposit_paid' => true,
            'status' => 'in_progress',
        ]);

        Order::create([
            'tenant_id' => $tenant->id,
            'order_number' => 'BC-1093',
            'client_name' => 'Michael Chang',
            'client_email' => 'mchang@example.com',
            'client_phone' => '(555) 441-9922',
            'due_date' => now()->addDays(3)->format('Y-m-d'), // Due in 3 days
            'time_slot' => '10:00 AM',
            'fulfillment_type' => 'delivery',
            'delivery_address' => '124 Maple Street, Nashville, TN',
            'items' => [['name' => 'Medium 2 Tiered Cake', 'price' => 175.00], ['name' => 'Dozen Cupcakes', 'price' => 35.00]],
            'flavors' => ['Almond Elegance (LUX)', 'Red Velvet'],
            'frosting' => ['Cream Cheese'],
            'fillings' => ['Fudge'],
            'special_notes' => 'Floral theme for anniversary party',
            'allergies' => 'Tree Nuts',
            'subtotal' => 220.00,
            'discount_amount' => 0.00,
            'total_price' => 220.00,
            'deposit_amount' => 110.00,
            'deposit_paid' => false,
            'status' => 'new',
        ]);
    }
}
