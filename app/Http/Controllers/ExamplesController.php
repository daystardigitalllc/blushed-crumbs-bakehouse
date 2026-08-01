<?php

namespace App\Http\Controllers;

use App\Models\Tenant;

class ExamplesController extends Controller
{
    public function index()
    {
        // /examples is a main-site marketing page — a tenant subdomain hitting
        // this same route (e.g. sweetmagnolia.doughmain.pro/examples) should
        // 404 rather than show the cross-tenant showcase on their own site.
        if (app()->bound('tenant')) {
            abort(404);
        }

        $themes = Tenant::getAllThemes();

        $bakeries = Tenant::where('is_demo', true)
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function (Tenant $tenant) use ($themes) {
                $hero = $tenant->galleries()->where('is_hero', true)->first()
                    ?? $tenant->galleries()->first();

                return [
                    'name' => $tenant->name,
                    'location' => trim("{$tenant->city}, {$tenant->state}", ', '),
                    'specialty' => $tenant->getSiteContent('hero_subheading'),
                    'theme_name' => $themes[$tenant->theme_id]['name'] ?? $tenant->theme_id,
                    'image_url' => $hero->image_url ?? null,
                    'url' => $tenant->publicUrl(),
                ];
            });

        return view('brand.examples', compact('bakeries'));
    }
}
