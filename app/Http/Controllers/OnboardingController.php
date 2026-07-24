<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tenant;

class OnboardingController extends Controller
{
    /**
     * Show the onboarding wizard.
     */
    public function show(Request $request)
    {
        $tenant = auth()->user()->tenant;

        // If already completed, redirect to baker dashboard
        if ($tenant && $tenant->onboarding_completed) {
            return redirect('/dashboard');
        }

        $themes = Tenant::getStarterThemes();

        return view('onboarding.wizard', compact('tenant', 'themes'));
    }

    /**
     * Save onboarding data (business info, brand preferences).
     */
    public function save(Request $request)
    {
        $tenant = auth()->user()->tenant;

        $validated = $request->validate([
            'bakery_name' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'hours' => 'nullable|string|max:255',
            'about' => 'nullable|string|max:2000',
            'specialties' => 'nullable|string|max:500',
            'style' => 'nullable|string|in:luxury,rustic,modern,fun,minimal',
            'theme_id' => 'nullable|string',
            'logo' => 'nullable|image|max:10240',
            'product_images.*' => 'nullable|image|max:10240',
            'social_url' => 'nullable|string|max:500',
            'instagram_url' => 'nullable|string|max:500',
            'facebook_url' => 'nullable|string|max:500',
        ]);

        // Update tenant info
        if (!empty($validated['bakery_name'])) {
            $tenant->name = $validated['bakery_name'];
        }
        if (!empty($validated['phone'])) {
            $tenant->phone = $validated['phone'];
        }
        if (!empty($validated['email'])) {
            $tenant->email = $validated['email'];
        }

        // Handle Logo Upload
        if ($request->hasFile('logo')) {
            $logoFile = $request->file('logo');
            if ($logoFile->isValid()) {
                $filename = 'logo_' . $tenant->id . '_' . time() . '.' . $logoFile->getClientOriginalExtension();
                $destPath = public_path('uploads/logos');
                if (!file_exists($destPath)) {
                    mkdir($destPath, 0777, true);
                }
                $logoFile->move($destPath, $filename);
                $tenant->logo_path = 'uploads/logos/' . $filename;
            }
        }

        // Handle Product Images Uploads
        $galleryImages = $tenant->gallery_images ?? [];
        if ($request->hasFile('product_images')) {
            $destPath = public_path('uploads/gallery');
            if (!file_exists($destPath)) {
                mkdir($destPath, 0777, true);
            }

            foreach ($request->file('product_images') as $idx => $file) {
                if ($file && $file->isValid()) {
                    $filename = 'product_' . $tenant->id . '_' . time() . '_' . $idx . '.' . $file->getClientOriginalExtension();
                    $file->move($destPath, $filename);
                    $path = 'uploads/gallery/' . $filename;
                    $galleryImages[] = $path;

                    \App\Models\GalleryItem::create([
                        'tenant_id' => $tenant->id,
                        'title' => $tenant->name . ' Creation',
                        'category' => 'Pastries',
                        'image_url' => $path,
                    ]);
                }
            }
        }

        // Handle Social Links & Auto-Importer
        $socialUrl = $request->input('social_url') ?? $request->input('instagram_url') ?? $request->input('facebook_url');
        if (!empty($socialUrl)) {
            if (str_contains($socialUrl, 'instagram.com')) {
                $tenant->instagram_url = $socialUrl;
            } elseif (str_contains($socialUrl, 'facebook.com')) {
                $tenant->facebook_url = $socialUrl;
            }

            $socialImporter = app(\App\Services\SocialImporterService::class);
            $imported = $socialImporter->importFromSocialUrl($socialUrl);

            if (!empty($imported['images'])) {
                foreach ($imported['images'] as $impImg) {
                    if (!in_array($impImg, $galleryImages)) {
                        $galleryImages[] = $impImg;
                        \App\Models\GalleryItem::create([
                            'tenant_id' => $tenant->id,
                            'title' => $tenant->name . ' Social Post',
                            'category' => 'Social',
                            'image_url' => $impImg,
                        ]);
                    }
                }
            }

            if (!empty($imported['about']) && empty($validated['about'])) {
                $validated['about'] = $imported['about'];
            }
        }

        $tenant->gallery_images = array_values(array_unique($galleryImages));

        // Store business info in site_content
        $content = $tenant->site_content ?? Tenant::getDefaultSiteContent();
        $content['contact_location'] = $validated['location'] ?? $content['contact_location'] ?? '';
        $content['contact_hours'] = $validated['hours'] ?? $content['contact_hours'] ?? '';
        $content['about_bio'] = $validated['about'] ?? $content['about_bio'] ?? '';
        $content['hero_headline'] = $validated['bakery_name'] ?? $content['hero_headline'] ?? '';
        if ($tenant->facebook_url) {
            $content['contact_facebook'] = $tenant->facebook_url;
        }
        if ($tenant->instagram_url) {
            $content['contact_instagram'] = $tenant->instagram_url;
        }

        $tenant->site_content = $content;

        // Set theme if chosen (only Starter themes for Starter plan)
        if (!empty($validated['theme_id'])) {
            $starterThemeKeys = array_keys(Tenant::getStarterThemes());
            if ($tenant->plan_tier === 'pro' || in_array($validated['theme_id'], $starterThemeKeys)) {
                $tenant->theme_id = $validated['theme_id'];
            } else {
                $tenant->theme_id = 'rustic_kitchen';
            }
        }

        $tenant->save();

        return response()->json([
            'success' => true,
            'message' => 'Business info & media saved!',
            'logo_path' => $tenant->logo_path,
            'gallery_images' => $tenant->gallery_images,
        ]);
    }

    /**
     * Import social photos via AJAX.
     */
    public function importSocial(Request $request)
    {
        $url = $request->input('url');
        if (empty($url)) {
            return response()->json(['success' => false, 'message' => 'Please provide a valid Instagram or Facebook URL.'], 400);
        }

        $importer = app(\App\Services\SocialImporterService::class);
        $res = $importer->importFromSocialUrl($url);

        return response()->json($res);
    }

    /**
     * Generate AI content for the bakery website.
     * This sends structured data to Google Gemini API and stores the result.
     */
    public function generate(Request $request)
    {
        $tenant = auth()->user()->tenant;
        $content = $tenant->site_content ?? Tenant::getDefaultSiteContent();

        // Save theme choice if passed from onboarding step 3
        $themeId = $request->input('theme_id');
        if (!empty($themeId)) {
            $tenant->theme_id = $themeId;
        }

        $style = $request->input('style', 'modern');

        // Generate tailored website copy using AiContentService (Gemini API with rich smart fallback)
        $aiService = app(\App\Services\AiContentService::class);
        $generated = $aiService->generateWebsiteContent([
            'name' => $tenant->name,
            'location' => $content['contact_location'] ?? '',
            'hours' => $content['contact_hours'] ?? '',
            'about' => $content['about_bio'] ?? '',
            'email' => $tenant->email,
            'phone' => $tenant->phone,
        ], [
            'style' => $style,
        ]);

        if (!empty($generated)) {
            $content = array_merge($content, $generated);
            $tenant->ai_generated_content = $generated;
        }

        $tenant->site_content = $content;
        $tenant->save();

        return response()->json([
            'success' => true,
            'message' => 'Website content generated!',
            'content' => $content,
            'theme_id' => $tenant->theme_id,
        ]);
    }

    /**
     * Publish the bakery website (mark onboarding as complete).
     */
    public function publish(Request $request)
    {
        $tenant = auth()->user()->tenant;

        $tenant->update([
            'onboarding_completed' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Your bakery website is live! 🎉',
            'redirect' => '/dashboard',
        ]);
    }
}
