<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Onboarding\OnboardingDraft;
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
        // Save selected plan preference in site_content, but DO NOT upgrade plan_tier to 'pro' until Stripe payment callback!
        $content = $tenant->site_content ?? Tenant::getDefaultSiteContent();
        if ($request->has('plan_tier')) {
            $content['selected_plan'] = $request->input('plan_tier') === 'pro' ? 'pro' : 'free';
        }

        // Handle Logo Upload
        if ($request->hasFile('logo')) {
            $logoFile = $request->file('logo');
            if ($logoFile->isValid()) {
                $filename = 'logo_' . $tenant->id . '_' . time() . '.' . $logoFile->getClientOriginalExtension();
                $destPath = public_path('uploads/tenants/' . $tenant->id . '/logos');
                if (!file_exists($destPath)) {
                    mkdir($destPath, 0755, true);
                }
                $logoFile->move($destPath, $filename);
                $tenant->logo_path = 'uploads/tenants/' . $tenant->id . '/logos/' . $filename;
            }
        }

        // Handle Product Images Uploads
        $galleryImages = $tenant->gallery_images ?? [];
        if ($request->hasFile('product_images')) {
            $destPath = public_path('uploads/tenants/' . $tenant->id . '/gallery');
            if (!file_exists($destPath)) {
                mkdir($destPath, 0755, true);
            }

            foreach ($request->file('product_images') as $idx => $file) {
                if ($file && $file->isValid()) {
                    $filename = 'product_' . $tenant->id . '_' . time() . '_' . $idx . '.' . $file->getClientOriginalExtension();
                    $file->move($destPath, $filename);
                    $path = 'uploads/tenants/' . $tenant->id . '/gallery/' . $filename;
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

        // Set theme if chosen
        if (!empty($validated['theme_id'])) {
            $this->applyThemeChoice($tenant, $validated['theme_id']);
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
     * The single gate for every theme selection in the legacy wizard.
     * Deliberately checks ONLY the tenant's real, paid plan_tier — never the
     * self-reported `selected_plan` in site_content, which a baker fully
     * controls client-side and previously granted a Pro theme for free (the
     * Phase 9 "Stripe fix" bug: save() and generate() both had their own
     * copy of this same trust-the-client bypass). An unpaid Pro pick is
     * stashed on `pending_pro_theme_id` instead of discarded, so paying
     * later applies it automatically (see StripeWebhookController).
     */
    private function applyThemeChoice(Tenant $tenant, string $themeId): void
    {
        $starterThemeKeys = array_keys(Tenant::getStarterThemes());

        if ($tenant->plan_tier === 'pro' || in_array($themeId, $starterThemeKeys, true)) {
            $tenant->theme_id = $themeId;

            return;
        }

        $tenant->pending_pro_theme_id = $themeId;
        if (empty($tenant->theme_id) || !in_array($tenant->theme_id, $starterThemeKeys, true)) {
            $tenant->theme_id = 'rustic_kitchen';
        }
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
            $this->applyThemeChoice($tenant, $themeId);
        }

        if ($request->has('plan_tier')) {
            $content['selected_plan'] = $request->input('plan_tier') === 'pro' ? 'pro' : 'free';
        }

        $style = $request->input('style', 'modern');

        // Generate tailored website copy using AiContentService (Gemini API with rich smart fallback)
        $aiService = app(\App\Services\AiContentService::class);
        $generated = $aiService->generateWebsiteContent([
            'tenant_id' => $tenant->id,
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
        $siteContent = $tenant->site_content ?? [];
        $selectedPlan = $request->input('plan_tier') ?? ($siteContent['selected_plan'] ?? 'free');

        // Import ALWAYS completes now, regardless of payment status — this is
        // the Phase 9 fix for the old Pro-plan deadlock, where choosing Pro
        // and abandoning the Stripe page left the tenant stuck mid-wizard
        // forever with onboarding_completed never set. The theme actually
        // applied was already safely gated in applyThemeChoice() (starter
        // fallback if unpaid, with the real choice stashed on
        // pending_pro_theme_id); Stripe is now a post-launch upsell, never a
        // blocker to going live.
        $tenant->update([
            'onboarding_completed' => true,
        ]);

        if (!empty($tenant->custom_domain)) {
            $domain = preg_replace('#^https?://#', '', trim($tenant->custom_domain, '/'));
            $redirectUrl = 'https://' . $domain;
        } else {
            $brandDomain = $tenant->brand?->domain ?? 'doughmain.pro';
            $redirectUrl = 'https://' . $tenant->subdomain . '.' . $brandDomain;
        }

        $response = [
            'success' => true,
            'message' => 'Your bakery website is live! 🎉',
            'redirect' => $redirectUrl,
        ];

        // Non-blocking upsell: only offered if they actually wanted Pro and
        // haven't paid — never withholds the redirect above.
        if ($selectedPlan === 'pro' && $tenant->plan_tier !== 'pro') {
            $response['stripe_upsell_url'] = 'https://buy.stripe.com/eVq00jeoj4aB62QanW2Ry0k?client_reference_id=' . $tenant->id . '&prefilled_email=' . urlencode($tenant->email ?? '');
            $response['message'] = 'Your bakery website is live! 🎉 Upgrade to Pro anytime to unlock your chosen theme.';
        }

        return response()->json($response);
    }

    /**
     * Resume an onboarding draft from an emailed link. Requires BOTH the
     * token AND an active login — the email itself could be forwarded, so
     * the token alone must never be sufficient (see the Phase 9 plan).
     * A missing/foreign/expired draft all render the same friendly page
     * rather than leaking which case it was or throwing a 500.
     */
    public function resume(Request $request, string $token)
    {
        $tenantId = auth()->user()->tenant_id;
        $draft = OnboardingDraft::where('resume_token', $token)->where('tenant_id', $tenantId)->first();

        if (!$draft || $this->isExpired($draft)) {
            return view('onboarding.resume-expired');
        }

        $draft->last_activity_at = now();
        $draft->save();

        return redirect()->route('onboarding.v2.wizard', ['draft' => $draft->id]);
    }

    private function isExpired(OnboardingDraft $draft): bool
    {
        if (!in_array($draft->status, OnboardingDraft::INCOMPLETE_STATUSES, true)) {
            return false; // imported/importing drafts aren't on the resume-token clock
        }

        $lastActivity = $draft->last_activity_at ?? $draft->created_at;
        $ttlHours = (int) config('onboarding.incomplete_draft_ttl_hours', 48);

        return $lastActivity !== null && $lastActivity->lt(now()->subHours($ttlHours));
    }

    /**
     * The browser's return trip from Stripe Checkout. Deliberately does
     * NOTHING to grant Pro — it never did anything trustworthy anyway
     * (client_reference_id straight off the query string, no signature, no
     * proof a payment happened). Actual Pro activation now happens
     * exclusively via the signature-verified StripeWebhookController, which
     * Stripe calls server-to-server and typically completes before this
     * browser redirect even lands. This route only exists to send the baker
     * somewhere sensible with a "hang tight" message.
     */
    public function stripeCallback(Request $request)
    {
        if (auth()->check()) {
            return redirect('/dashboard')->with('success', '🎉 Payment received! Your Pro upgrade is being activated — this usually takes just a few seconds.');
        }

        return redirect('/login')->with('info', 'Payment received! Please log in — your Pro upgrade will be active shortly.');
    }
}
