<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Models\Tenant;
use App\Models\Customer;
use App\Models\EmailSubscriber;
use App\Models\EmailCampaign;
use App\Mail\PromoEmail;

class EmailMarketingController extends Controller
{
    /**
     * A hard cap on synchronous sends per request. No confirmed queue
     * worker consumes the default queue on this server (only `ingest` and
     * `ai-import` have a daemon), so campaigns are sent inline like
     * NewOrderNotification rather than assuming a queue drains them. This
     * cap keeps a large list from timing out the HTTP request; anything
     * larger should be split into multiple sends.
     */
    private const MAX_SYNC_RECIPIENTS = 500;

    private function tenant(Request $request): Tenant
    {
        if ($request->attributes->get('tenant')) {
            return $request->attributes->get('tenant');
        }

        return auth()->user()?->tenant ?? Tenant::first();
    }

    private function requirePro(Tenant $tenant)
    {
        if ($tenant->plan_tier !== 'pro') {
            return response()->json([
                'success' => false,
                'message' => 'Email Marketing is available on Doughmain Pro only. Upgrade to Pro to build a subscriber list and send offers.',
            ], 403);
        }

        return null;
    }

    public function storeSubscriber(Request $request)
    {
        $tenant = $this->tenant($request);
        if ($blocked = $this->requirePro($tenant)) {
            return $blocked;
        }

        $validated = $request->validate([
            'email' => 'required|email|max:255',
            'name' => 'nullable|string|max:255',
        ]);

        $subscriber = EmailSubscriber::updateOrCreate(
            ['tenant_id' => $tenant->id, 'email' => strtolower($validated['email'])],
            ['name' => $validated['name'] ?? null, 'source' => 'manual', 'unsubscribed_at' => null]
        );

        return response()->json(['success' => true, 'subscriber' => $subscriber]);
    }

    public function destroySubscriber(Request $request, EmailSubscriber $subscriber)
    {
        $tenant = $this->tenant($request);
        if ($blocked = $this->requirePro($tenant)) {
            return $blocked;
        }

        if ($subscriber->tenant_id !== $tenant->id) {
            abort(404);
        }

        $subscriber->delete();

        return response()->json(['success' => true]);
    }

    /**
     * One-click bulk-add every existing Customer with an email address that
     * isn't already on the list — the fastest way for a baker to get a
     * useful list going without manual entry.
     */
    public function importCustomers(Request $request)
    {
        $tenant = $this->tenant($request);
        if ($blocked = $this->requirePro($tenant)) {
            return $blocked;
        }

        $customers = Customer::where('tenant_id', $tenant->id)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->get();

        $imported = 0;
        foreach ($customers as $customer) {
            $email = strtolower($customer->email);
            $existing = EmailSubscriber::where('tenant_id', $tenant->id)->where('email', $email)->first();
            if ($existing) {
                continue;
            }

            EmailSubscriber::create([
                'tenant_id' => $tenant->id,
                'email' => $email,
                'name' => $customer->name,
                'source' => 'customer_import',
            ]);
            $imported++;
        }

        return response()->json([
            'success' => true,
            'imported' => $imported,
            'message' => $imported > 0
                ? "Imported {$imported} customer" . ($imported === 1 ? '' : 's') . " into your subscriber list."
                : 'No new customers to import — everyone with an email is already on your list.',
        ]);
    }

    /**
     * Compose + immediately send a promo campaign to every active
     * subscriber. Sent synchronously in a loop (see MAX_SYNC_RECIPIENTS) —
     * one failed address doesn't stop the rest.
     */
    public function storeCampaign(Request $request)
    {
        $tenant = $this->tenant($request);
        if ($blocked = $this->requirePro($tenant)) {
            return $blocked;
        }

        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'body' => 'required|string|max:5000',
            'coupon_code' => 'nullable|string|max:50',
        ]);

        $subscribers = EmailSubscriber::where('tenant_id', $tenant->id)->active()->get();

        if ($subscribers->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'You have no active subscribers yet. Add some or import from your customers first.',
            ], 422);
        }

        if ($subscribers->count() > self::MAX_SYNC_RECIPIENTS) {
            return response()->json([
                'success' => false,
                'message' => "Your list has {$subscribers->count()} subscribers, above the " . self::MAX_SYNC_RECIPIENTS . " per-send limit. Contact support to send larger campaigns.",
            ], 422);
        }

        $campaign = EmailCampaign::create([
            'tenant_id' => $tenant->id,
            'subject' => $validated['subject'],
            'body' => $validated['body'],
            'coupon_code' => $validated['coupon_code'] ?? null,
            'status' => 'sending',
            'recipient_count' => $subscribers->count(),
        ]);

        $sent = 0;
        foreach ($subscribers as $subscriber) {
            try {
                Mail::to($subscriber->email)->send(new PromoEmail($tenant, $campaign, $subscriber));
                $sent++;
            } catch (\Throwable $e) {
                report($e);
            }
        }

        $campaign->update([
            'status' => $sent > 0 ? 'sent' : 'failed',
            'sent_count' => $sent,
            'sent_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'campaign' => $campaign,
            'message' => "Sent to {$sent} of {$subscribers->count()} subscribers.",
        ]);
    }

    /**
     * Public storefront signup — no auth, tenant resolved by ResolveTenant
     * from the subdomain like every other storefront route.
     */
    public function subscribe(Request $request)
    {
        $tenant = $request->attributes->get('tenant');
        if (!$tenant) {
            abort(404);
        }

        $validated = $request->validate([
            'email' => 'required|email|max:255',
            'name' => 'nullable|string|max:255',
        ]);

        EmailSubscriber::updateOrCreate(
            ['tenant_id' => $tenant->id, 'email' => strtolower($validated['email'])],
            ['name' => $validated['name'] ?? null, 'source' => 'storefront_signup', 'unsubscribed_at' => null]
        );

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => "Thanks! You're subscribed to {$tenant->name}'s offers."]);
        }

        return back()->with('subscribed', true);
    }

    /**
     * Public, token-based (not signed-URL) unsubscribe — the token itself
     * is the unguessable secret, same trust model as a typical mailing-list
     * unsubscribe link, so it works from any mail client without needing a
     * session or a Laravel-signed URL that could expire.
     */
    public function unsubscribe(string $token)
    {
        $subscriber = EmailSubscriber::withoutGlobalScopes()->where('unsubscribe_token', $token)->first();

        if (!$subscriber) {
            abort(404);
        }

        $subscriber->update(['unsubscribed_at' => now()]);

        return view('emails.unsubscribed', ['tenant' => $subscriber->tenant]);
    }
}
