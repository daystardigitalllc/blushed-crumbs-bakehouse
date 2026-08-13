<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tenant;
use App\Models\Customer;
use App\Models\EmailSubscriber;
use App\Models\EmailCampaign;
use App\Jobs\SendPromoEmailJob;

class EmailMarketingController extends Controller
{
    /**
     * Sanity ceiling, not a technical one — sends are queued (see the
     * dedicated `emails` Forge daemon), so list size no longer risks an
     * HTTP timeout. This just stops one campaign from silently queuing an
     * unreasonable number of jobs in one shot.
     */
    private const MAX_RECIPIENTS = 20000;

    private function tenant(Request $request): Tenant
    {
        if ($request->attributes->get('tenant')) {
            return $request->attributes->get('tenant');
        }

        return auth()->user()?->tenant ?? Tenant::first();
    }

    /**
     * Only sending is Pro-gated — collection (add/import/remove
     * subscribers) is free for every tenant so a baker builds a real list
     * before she ever hits a paywall. Letting her watch that list grow with
     * a locked "Send" button is a stronger upgrade prompt than hiding the
     * whole feature: she's already invested the effort, and the value
     * (an audience to email) is concrete and hers, not a generic pitch.
     */
    private function requirePro(Tenant $tenant)
    {
        if ($tenant->plan_tier !== 'pro') {
            return response()->json([
                'success' => false,
                'message' => 'Sending campaigns is available on Doughmain Pro only. Your subscriber list is yours to keep growing free — upgrade to Pro to actually send to it.',
            ], 403);
        }

        return null;
    }

    public function storeSubscriber(Request $request)
    {
        $tenant = $this->tenant($request);

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

        $customers = Customer::where('tenant_id', $tenant->id)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->get();

        $importedSubscribers = collect();
        foreach ($customers as $customer) {
            $email = strtolower($customer->email);
            $existing = EmailSubscriber::where('tenant_id', $tenant->id)->where('email', $email)->first();
            if ($existing) {
                continue;
            }

            $importedSubscribers->push(EmailSubscriber::create([
                'tenant_id' => $tenant->id,
                'email' => $email,
                'name' => $customer->name,
                'source' => 'customer_import',
            ]));
        }

        $imported = $importedSubscribers->count();

        return response()->json([
            'success' => true,
            'imported' => $imported,
            'subscribers' => $importedSubscribers->values(),
            'message' => $imported > 0
                ? "Imported {$imported} customer" . ($imported === 1 ? '' : 's') . " into your subscriber list."
                : 'No new customers to import — everyone with an email is already on your list.',
        ]);
    }

    /**
     * Compose a campaign and queue one SendPromoEmailJob per active
     * subscriber (see the `emails` Forge daemon) — the request returns as
     * soon as the jobs are queued; sent_count/failed_count/status update
     * asynchronously as the worker processes them.
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

        if ($subscribers->count() > self::MAX_RECIPIENTS) {
            return response()->json([
                'success' => false,
                'message' => "Your list has {$subscribers->count()} subscribers, above the " . self::MAX_RECIPIENTS . " per-send limit. Contact support to send larger campaigns.",
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

        foreach ($subscribers as $subscriber) {
            SendPromoEmailJob::dispatch($tenant->id, $campaign->id, $subscriber->id)->onQueue('emails');
        }

        return response()->json([
            'success' => true,
            'campaign' => $campaign,
            'message' => "Sending to {$subscribers->count()} subscribers now — refresh in a moment to see delivery status.",
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
