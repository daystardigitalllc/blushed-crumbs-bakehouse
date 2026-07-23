<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AiContentService
{
    protected string $apiKey;
    protected string $model;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.key', env('GEMINI_API_KEY', ''));
        $this->model = config('services.gemini.model', env('GEMINI_MODEL', 'gemini-3.5-flash-lite'));
    }

    /**
     * Generate website content using Google Gemini API.
     *
     * @param array $businessInfo  Name, location, hours, about, specialties
     * @param array $preferences   Style, colors, personality
     * @return array
     */
    public function generateWebsiteContent(array $businessInfo, array $preferences): array
    {
        if (!empty($this->apiKey)) {
            $prompt = $this->buildPrompt($businessInfo, $preferences);
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}";

            try {
                $response = Http::withoutVerifying()->withHeaders([
                    'Content-Type' => 'application/json',
                ])->timeout(15)->post($url, [
                    'contents' => [
                        [
                            'parts' => [
                                [
                                    'text' => "You are a professional bakery website copywriter. Generate website content in JSON format only. No markdown fences — just raw valid JSON.\n\n" . $prompt,
                                ],
                            ],
                        ],
                    ],
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'responseMimeType' => 'application/json',
                    ],
                ]);

                if ($response->successful()) {
                    $text = $response->json('candidates.0.content.parts.0.text');
                    if ($text) {
                        $cleanJson = preg_replace('/^```(?:json)?\s*/i', '', trim($text));
                        $cleanJson = preg_replace('/\s*```$/', '', $cleanJson);
                        $parsed = json_decode($cleanJson, true);
                        if (is_array($parsed)) {
                            return $this->mapToSiteContent($parsed, $businessInfo);
                        }
                    }
                } else {
                    \Illuminate\Support\Facades\Log::warning('Gemini API status ' . $response->status() . ': ' . $response->body());
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Gemini API Error: ' . $e->getMessage());
            }
        }

        // Return rich, tailored fallback copy if API fails or credit depleted
        return $this->generateRichFallbackContent($businessInfo, $preferences);
    }

    protected function generateRichFallbackContent(array $business, array $prefs): array
    {
        $name = !empty($business['name']) ? $business['name'] : 'Our Artisanal Bakery';
        $location = !empty($business['location']) ? $business['location'] : 'Local Bakery';
        $aboutUser = !empty($business['about']) ? $business['about'] : '';
        $style = $prefs['style'] ?? 'modern';

        $subheadings = [
            'rustic' => 'Handcrafted Daily With Organic Flour & Love',
            'luxury' => 'Artisanal Patisserie & Bespoke Celebration Cakes',
            'fun' => 'Freshly Baked Treats & Whimsical Custom Creations',
            'modern' => 'Artisanal Bakery & Custom Cake Studio',
        ];

        $aboutBios = [
            'rustic' => $aboutUser ?: "At {$name}, we believe in simple ingredients, long fermentation, and old-world baking traditions. Based in {$location}, we bring warm, crusty breads and delicate pastries to our community every morning.",
            'luxury' => $aboutUser ?: "Welcome to {$name}. Located in {$location}, we specialize in masterfully crafted custom cakes, elegant dessert tables, and refined French pastries designed to make every milestone unforgettable.",
            'fun' => $aboutUser ?: "{$name} is your colorful neighborhood bakery in {$location}! We bake delicious custom cakes, decadent cupcakes, and whimsical sweet treats that put a smile on every face.",
            'modern' => $aboutUser ?: "At {$name} in {$location}, we blend modern flavor profiles with classic baking techniques. Every item is crafted fresh daily using locally sourced ingredients.",
        ];

        return [
            'hero_subheading' => $subheadings[$style] ?? 'Freshly Baked Daily With Premium Ingredients',
            'hero_headline' => $name,
            'hero_cta_primary' => 'Order Custom Cake',
            'hero_cta_secondary' => 'Explore Pastry Menu',
            'about_title' => "The Story of {$name}",
            'about_bio' => $aboutBios[$style] ?? "Crafted with passion in {$location}. Welcome to {$name}.",
            'highlights' => [
                ['icon' => '🥖', 'title' => 'Baked Fresh Daily', 'desc' => 'Handcrafted small-batch treats every morning.'],
                ['icon' => '🎂', 'title' => 'Custom Celebration Cakes', 'desc' => 'Tailored flavors, tiers, and hand-piped designs.'],
                ['icon' => '🌾', 'title' => 'Organic & Local', 'desc' => 'Sourced from local farms and premium ingredients.'],
                ['icon' => '🚚', 'title' => 'Pickup & Delivery', 'desc' => 'Seamless online scheduling and door-to-door delivery.'],
            ],
            'promo_headline' => 'Order Early For Your Next Milestone Event',
            'promo_subtext' => 'We recommend booking custom orders 2-3 weeks in advance.',
            'how_it_works' => [
                ['title' => '1. Select Design & Flavors', 'desc' => 'Choose your cake size, sponge flavors, and decadent fillings.'],
                ['title' => '2. Set Date & Details', 'desc' => 'Specify pickup/delivery date, allergies, and custom notes.'],
                ['title' => '3. Baked & Hand-Delivered', 'desc' => 'We craft your dream cake fresh for your special day.'],
            ],
            'whimsical_title' => "Why Bakers & Customers Love {$name}",
            'whimsical_bullets' => [
                '100% scratch-made with real butter & Madagascar vanilla',
                'Custom dietary options: Gluten-Free, Vegan & Nut-Free available',
                'Fast custom invoicing and secure digital payments',
            ],
            'categories' => [
                ['title' => 'Single Tier Cakes', 'desc' => 'Perfect for birthdays & intimate gatherings'],
                ['title' => 'Multi Tier Custom Cakes', 'desc' => 'Bespoke designs for weddings & celebrations'],
                ['title' => 'Treats & Sweets By The Dozen', 'desc' => 'Cupcakes, macarons, and dessert tables'],
            ],
            'reviews' => [
                ['name' => 'Sarah M.', 'quote' => 'The custom cake for our celebration was absolute perfection! Tasted even better than it looked!'],
                ['name' => 'Jessica & David K.', 'quote' => 'Hands down the best pastries and baked goods in town. Fresh, flavorful, and stunning presentation!'],
                ['name' => 'Emily R.', 'quote' => 'Ordering online was effortless and pickup was smooth. Our guests raved about the dessert table!'],
            ],
            'cta_headline' => 'Ready to Taste the Difference?',
            'cta_subtext' => 'Order your custom cake or dessert table today!',
            'cta_btn_text' => 'Start Your Order Now',
        ];
    }

    protected function buildPrompt(array $business, array $prefs): string
    {
        $name = $business['name'] ?? 'Our Bakery';
        $location = $business['location'] ?? '';
        $about = $business['about'] ?? '';
        $style = $prefs['style'] ?? 'modern';

        return <<<PROMPT
Generate bakery website content for "{$name}".
Location: {$location}
About: {$about}
Brand Style: {$style}

Return JSON with these exact keys:
{
  "hero_subheading": "short tagline (5-8 words)",
  "hero_headline": "{$name}",
  "hero_cta_primary": "primary button text",
  "hero_cta_secondary": "secondary button text",
  "about_title": "about section heading",
  "about_bio": "2-3 sentence bakery description",
  "highlights": [
    {"icon": "emoji", "title": "short title", "desc": "one sentence"},
    {"icon": "emoji", "title": "short title", "desc": "one sentence"},
    {"icon": "emoji", "title": "short title", "desc": "one sentence"},
    {"icon": "emoji", "title": "short title", "desc": "one sentence"}
  ],
  "promo_headline": "promotional banner headline",
  "promo_subtext": "promotional banner subtext",
  "how_it_works": [
    {"title": "step 1 title", "desc": "step 1 description"},
    {"title": "step 2 title", "desc": "step 2 description"},
    {"title": "step 3 title", "desc": "step 3 description"}
  ],
  "categories": [
    {"title": "category 1 title", "desc": "short category description"},
    {"title": "category 2 title", "desc": "short category description"},
    {"title": "category 3 title", "desc": "short category description"}
  ],
  "reviews": [
    {"name": "Customer Name", "quote": "enthusiastic customer review specific to this bakery style"},
    {"name": "Customer Name", "quote": "enthusiastic customer review specific to this bakery style"},
    {"name": "Customer Name", "quote": "enthusiastic customer review specific to this bakery style"}
  ],
  "whimsical_title": "specialties section title",
  "whimsical_bullets": ["bullet 1", "bullet 2", "bullet 3"],
  "cta_headline": "footer CTA headline",
  "cta_subtext": "footer CTA subtext",
  "cta_btn_text": "footer CTA button text",
  "seo_title": "page title for SEO",
  "seo_description": "meta description for SEO"
}
PROMPT;
    }

    protected function mapToSiteContent(array $ai, array $business): array
    {
        return [
            'hero_subheading' => $ai['hero_subheading'] ?? 'Order For Any Occasion',
            'hero_headline' => $ai['hero_headline'] ?? $business['name'],
            'hero_cta_primary' => $ai['hero_cta_primary'] ?? 'Order Now',
            'hero_cta_secondary' => $ai['hero_cta_secondary'] ?? 'Our Menu',
            'about_title' => $ai['about_title'] ?? 'About Us',
            'about_bio' => $ai['about_bio'] ?? '',
            'highlights' => $ai['highlights'] ?? [],
            'promo_headline' => $ai['promo_headline'] ?? '',
            'promo_subtext' => $ai['promo_subtext'] ?? '',
            'how_it_works' => $ai['how_it_works'] ?? [],
            'categories' => $ai['categories'] ?? [],
            'reviews' => $ai['reviews'] ?? [],
            'whimsical_title' => $ai['whimsical_title'] ?? '',
            'whimsical_bullets' => $ai['whimsical_bullets'] ?? [],
            'cta_headline' => $ai['cta_headline'] ?? '',
            'cta_subtext' => $ai['cta_subtext'] ?? '',
            'cta_btn_text' => $ai['cta_btn_text'] ?? 'Order Now',
        ];
    }
}
