<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tenant;

class LegalController extends Controller
{
    /**
     * Master Legal Document Registry
     */
    public static function getDocuments(): array
    {
        return [
            'terms' => [
                'slug' => 'terms',
                'title' => 'Terms of Service',
                'subtitle' => 'Master Subscription & Platform Agreement',
                'updated' => 'July 24, 2026',
                'category' => 'General Terms',
                'icon' => '📜',
            ],
            'privacy' => [
                'slug' => 'privacy',
                'title' => 'Privacy Policy',
                'subtitle' => 'Data Collection, Storage & Privacy Rights',
                'updated' => 'July 24, 2026',
                'category' => 'Privacy & Security',
                'icon' => '🔒',
            ],
            'acceptable-use' => [
                'slug' => 'acceptable-use',
                'title' => 'Acceptable Use Policy (AUP)',
                'subtitle' => 'Platform Conduct, Prohibited Items & Content Standards',
                'updated' => 'July 24, 2026',
                'category' => 'Security & Compliance',
                'icon' => '🛡️',
            ],
            'cookie-policy' => [
                'slug' => 'cookie-policy',
                'title' => 'Cookie & Tracking Policy',
                'subtitle' => 'Session Management, Cookies & Local Storage Use',
                'updated' => 'July 24, 2026',
                'category' => 'Privacy & Security',
                'icon' => '🍪',
            ],
            'dmca' => [
                'slug' => 'dmca',
                'title' => 'DMCA & Copyright Policy',
                'subtitle' => 'Copyright Infringement Takedown Procedures',
                'updated' => 'July 24, 2026',
                'category' => 'Intellectual Property',
                'icon' => '⚖️',
            ],
            'refund-policy' => [
                'slug' => 'refund-policy',
                'title' => 'Refund & Cancellation Policy',
                'subtitle' => 'Subscription Renewal, Cancellations & Refund Rules',
                'updated' => 'July 24, 2026',
                'category' => 'Billing & Financial',
                'icon' => '💳',
            ],
            'sla' => [
                'slug' => 'sla',
                'title' => 'Service Level Disclaimer (SLA)',
                'subtitle' => 'Platform Availability, Maintenance & Outage Disclaimers',
                'updated' => 'July 24, 2026',
                'category' => 'Operations',
                'icon' => '⚡',
            ],
            'dpa' => [
                'slug' => 'dpa',
                'title' => 'Data Processing Addendum (DPA)',
                'subtitle' => 'Customer Data Processing & Subprocessor Schedule',
                'updated' => 'July 24, 2026',
                'category' => 'Privacy & Security',
                'icon' => '📁',
            ],
            'ai-policy' => [
                'slug' => 'ai-policy',
                'title' => 'AI Usage Policy & Content Disclaimer',
                'subtitle' => 'AI Generation Guidelines, Accuracy & Allergen Responsibility',
                'updated' => 'July 24, 2026',
                'category' => 'AI & Content',
                'icon' => '🤖',
            ],
            'domain-policy' => [
                'slug' => 'domain-policy',
                'title' => 'Domain & DNS Management Policy',
                'subtitle' => 'Custom Domain Ownership, DNS Records & SSL Provisioning',
                'updated' => 'July 24, 2026',
                'category' => 'Infrastructure',
                'icon' => '🌐',
            ],
        ];
    }

    /**
     * Render Legal Hub view listing all 10 documents.
     */
    public function index(Request $request)
    {
        $tenant = $request->attributes->get('tenant');
        $documents = static::getDocuments();

        return view('legal.index', compact('tenant', 'documents'));
    }

    /**
     * Render a specific legal document by slug.
     */
    public function show(Request $request, string $slug)
    {
        $documents = static::getDocuments();

        if (!isset($documents[$slug])) {
            abort(404, 'Legal document not found.');
        }

        $document = $documents[$slug];
        $tenant = $request->attributes->get('tenant');

        return view('legal.index', compact('tenant', 'documents', 'document', 'slug'));
    }

    /**
     * Tenant preview version of a specific legal document by slug.
     */
    public function previewShow(Request $request, string $subdomain, string $slug)
    {
        $tenant = Tenant::where('subdomain', $subdomain)->orWhere('slug', $subdomain)->where('is_active', true)->first();
        if (!$tenant) {
            abort(404, 'Bakery website not found.');
        }

        $request->attributes->set('tenant', $tenant);
        app()->instance('tenant', $tenant);

        return $this->show($request, $slug);
    }

    /**
     * Data Privacy Export Endpoint (GDPR / CCPA data export).
     */
    public function exportData(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        $tenant = $user->tenant;
        $exportData = [
            'platform' => 'Doughmain.pro (Daystar Digital LLC)',
            'exported_at' => now()->toIso8601String(),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'created_at' => $user->created_at,
            ],
            'tenant' => $tenant ? [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'slug' => $tenant->slug,
                'subdomain' => $tenant->subdomain,
                'email' => $tenant->email,
                'phone' => $tenant->phone,
                'plan_tier' => $tenant->plan_tier,
                'custom_domain' => $tenant->custom_domain,
            ] : null,
        ];

        \App\Models\AuditLog::logEvent('data.export', $tenant?->id, $user->id, ['export_type' => 'user_data_export']);

        return response()->json($exportData, 200, [
            'Content-Disposition' => 'attachment; filename="doughmain_data_export_' . date('Y-m-d') . '.json"',
        ]);
    }

    /**
     * Data Privacy Deletion Request Endpoint.
     */
    public function requestDeletion(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        \App\Models\AuditLog::logEvent('data.deletion_request', $user->tenant_id, $user->id, [
            'user_email' => $user->email,
            'requested_at' => now()->toIso8601String(),
        ], 'warning');

        return response()->json([
            'success' => true,
            'message' => 'Your account data deletion request has been logged and submitted to Daystar Digital LLC compliance officers. A security confirmation email will be sent to your registered email address within 24 hours.',
        ]);
    }
}
