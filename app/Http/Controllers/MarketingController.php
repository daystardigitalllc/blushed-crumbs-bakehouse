<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MarketingController extends Controller
{
    /**
     * Display the Bakery Website Builder page.
     */
    public function bakeryWebsiteBuilder()
    {
        return view('brand.seo.bakery-website-builder');
    }

    /**
     * Display the Bakery Website Design page.
     */
    public function bakeryWebsiteDesign()
    {
        return view('brand.seo.bakery-website-design');
    }

    /**
     * Display the Home Bakery Website page.
     */
    public function homeBakeryWebsite()
    {
        return view('brand.seo.home-bakery-website');
    }

    /**
     * Display the Custom Cake Website page.
     */
    public function customCakeWebsite()
    {
        return view('brand.seo.custom-cake-website');
    }

    /**
     * Display the Bakesy Alternative comparison page.
     */
    public function bakesyAlternative()
    {
        return view('brand.seo.bakesy-alternative');
    }

    /**
     * Display the Bakebug Alternative comparison page.
     */
    public function bakebugAlternative()
    {
        return view('brand.seo.bakebug-alternative');
    }

    /**
     * Display the blog home page listing all articles.
     */
    public function blogIndex()
    {
        $posts = $this->getBlogPostsList();

        return view('blog.index', compact('posts'));
    }

    /**
     * Display a specific blog article by its slug.
     */
    public function blogPost($slug)
    {
        $posts = $this->getBlogPostsList();

        if (!isset($posts[$slug])) {
            abort(404, 'Blog article not found.');
        }

        $post = $posts[$slug];

        return view('blog.' . $slug, compact('post'));
    }

    /**
     * Predefined list of blog posts with metadata for index page.
     */
    private function getBlogPostsList(): array
    {
        return [
            'how-much-does-a-bakery-website-cost' => [
                'title' => 'How Much Does a Bakery Website Cost?',
                'description' => 'A complete guide to bakery web design costs. Compare hiring an agency, freelancers, DIY builders, and free platforms.',
                'emoji' => '💰',
                'category' => 'Business',
                'date' => 'July 31, 2026',
                'read_time' => '6 min read',
            ],
            'how-to-sell-cakes-online' => [
                'title' => 'How to Sell Cakes Online: The Complete Baker Guide',
                'description' => 'Learn step-by-step how to sell custom cakes online, handle payments securely, set order lead times, and manage orders.',
                'emoji' => '🎂',
                'category' => 'Guides',
                'date' => 'July 28, 2026',
                'read_time' => '8 min read',
            ],
            'how-to-get-more-wedding-cake-customers' => [
                'title' => 'How to Get More Wedding Cake Customers',
                'description' => 'Proven marketing strategies for home bakers to attract high-budget wedding couples, run cake tastings, and earn reviews.',
                'emoji' => '👰',
                'category' => 'Marketing',
                'date' => 'July 25, 2026',
                'read_time' => '7 min read',
            ],
            'how-to-rank-a-bakery-website-on-google' => [
                'title' => 'How to Rank a Bakery Website on Google: Local SEO',
                'description' => 'Learn the local SEO tactics that will put your bakery at the top of Google Search and Maps in your city.',
                'emoji' => '🔍',
                'category' => 'SEO',
                'date' => 'July 22, 2026',
                'read_time' => '9 min read',
            ],
            'do-home-bakers-need-a-website' => [
                'title' => 'Do Home Bakers Need a Website? (Social Media vs Website)',
                'description' => 'Is relying on Instagram DMs costing you business? We explore why home bakers need a professional website.',
                'emoji' => '🏠',
                'category' => 'Business',
                'date' => 'July 19, 2026',
                'read_time' => '5 min read',
            ],
            'the-best-website-platforms-for-bakeries' => [
                'title' => 'The Best Website Platforms for Bakeries Compared',
                'description' => 'We compare Squarespace, Shopify, Wix, Bakesy, and Doughmain to find the ultimate choice for bakery websites.',
                'emoji' => '🖥️',
                'category' => 'Reviews',
                'date' => 'July 16, 2026',
                'read_time' => '8 min read',
            ],
        ];
    }
}
