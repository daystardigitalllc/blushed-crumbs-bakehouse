<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SitemapController extends Controller
{
    /**
     * /sitemap.xml — host-aware via ResolveTenant, same as every other
     * storefront route: a tenant subdomain/custom domain gets a sitemap of
     * just that bakery's pages, the main doughmain.pro domain gets the
     * marketing + free-tools pages (including all 50 cottage-food-law
     * state pages, which are otherwise only reachable via the on-page
     * state picker).
     */
    public function sitemap(Request $request)
    {
        $tenant = $request->attributes->get('tenant');

        $urls = $tenant ? $this->tenantUrls($tenant) : $this->brandUrls();

        return response()
            ->view('sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml');
    }

    /**
     * /robots.txt — same host-awareness. Disallows the authenticated
     * admin/onboarding surfaces on both the main domain and every tenant
     * site, and points crawlers at the matching sitemap.
     */
    public function robots(Request $request)
    {
        $tenant = $request->attributes->get('tenant');
        $sitemapUrl = $tenant ? $tenant->publicUrl('/sitemap.xml') : url('/sitemap.xml');

        $disallow = $tenant
            ? ['/dashboard', '/admin', '/onboarding', '/login', '/register', '/order']
            : ['/dashboard', '/admin', '/onboarding', '/login', '/register', '/account', '/site/'];

        $lines = ['User-agent: *'];
        foreach ($disallow as $path) {
            $lines[] = "Disallow: {$path}";
        }
        $lines[] = '';
        $lines[] = "Sitemap: {$sitemapUrl}";

        return response(implode("\n", $lines))->header('Content-Type', 'text/plain');
    }

    /**
     * A tenant's own indexable pages. The policy page is only linked from
     * the nav for one legacy tenant (subdomain === 'blushedcrumbs', see the
     * theme templates) rather than being a general feature yet, so it's
     * left out of every other tenant's sitemap to match.
     */
    protected function tenantUrls($tenant): array
    {
        $paths = ['', '/about', '/menu', '/gallery'];

        if ($tenant->subdomain === 'blushedcrumbs') {
            $paths[] = '/policy';
        }

        $paths[] = '/privacy';
        $paths[] = '/terms';

        $lastmod = optional($tenant->updated_at)->toAtomString();

        return array_map(fn ($path) => [
            'loc' => $tenant->publicUrl($path),
            'lastmod' => $lastmod,
        ], $paths);
    }

    /**
     * The platform's own marketing/lead-gen pages.
     */
    protected function brandUrls(): array
    {
        $paths = [
            '/',
            '/landing',
            '/tools/bakery-pricing-calculator',
            '/cottage-food-laws',
            '/legal',
            '/bakery-website-builder',
            '/bakery-website-design',
            '/home-bakery-website',
            '/custom-cake-website',
            '/bakesy-alternative',
            '/bakebug-alternative',
            '/blog',
            '/blog/how-much-does-a-bakery-website-cost',
            '/blog/how-to-sell-cakes-online',
            '/blog/how-to-get-more-wedding-cake-customers',
            '/blog/how-to-rank-a-bakery-website-on-google',
            '/blog/do-home-bakers-need-a-website',
            '/blog/the-best-website-platforms-for-bakeries',
        ];

        foreach (CottageFoodLawsController::allStatesSummary() as $state) {
            $paths[] = '/cottage-food-laws/' . $state['slug'];
        }

        foreach (array_keys(LegalController::getDocuments()) as $slug) {
            $paths[] = '/legal/' . $slug;
        }

        return array_map(fn ($path) => [
            'loc' => url($path),
            'lastmod' => null,
        ], $paths);
    }
}
