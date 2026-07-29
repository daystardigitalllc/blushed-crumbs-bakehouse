<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Event;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

/**
 * Replaces the old trust-the-query-string `GET /stripe/callback` bypass
 * (OnboardingController::stripeCallback(), now inert). Only a request
 * bearing a Stripe-Signature that verifies against STRIPE_WEBHOOK_SECRET can
 * ever grant Pro — nothing here trusts client-supplied identifiers alone.
 */
class StripeWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $secret = config('services.stripe.webhook_secret');
        if (!$secret) {
            Log::error('Stripe webhook received but STRIPE_WEBHOOK_SECRET is not configured — refusing to process.');

            return response('Webhook not configured.', 500);
        }

        try {
            $event = Webhook::constructEvent($request->getContent(), $request->header('Stripe-Signature'), $secret);
        } catch (SignatureVerificationException | \UnexpectedValueException $e) {
            Log::warning('Stripe webhook signature verification failed.', ['error' => $e->getMessage()]);

            return response('Invalid signature.', 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $this->handleCheckoutCompleted($event);
        }

        return response('OK', 200);
    }

    private function handleCheckoutCompleted(Event $event): void
    {
        $session = $event->data->object;

        $paid = in_array($session->payment_status ?? null, ['paid', 'no_payment_required'], true);
        if (!$paid) {
            return;
        }

        $tenantId = $session->client_reference_id ?? null;
        if (!$tenantId || !is_numeric($tenantId)) {
            Log::warning('Stripe checkout.session.completed had no usable client_reference_id.', ['session_id' => $session->id ?? null]);

            return;
        }

        $tenant = Tenant::find($tenantId);
        if (!$tenant) {
            return;
        }

        $tenant->plan_tier = 'pro';

        // The theme the baker actually picked while unpaid (see Wizard/ImportDraftJob's
        // real-plan_tier gate) gets applied now instead of a starter fallback.
        if ($tenant->pending_pro_theme_id) {
            $tenant->theme_id = $tenant->pending_pro_theme_id;
            $tenant->pending_pro_theme_id = null;
        }

        if (!empty($session->customer)) {
            $tenant->stripe_customer_id = is_string($session->customer) ? $session->customer : $session->customer->id;
        }
        if (!empty($session->subscription)) {
            $tenant->stripe_subscription_id = is_string($session->subscription) ? $session->subscription : $session->subscription->id;
        }

        $tenant->save();

        AuditLog::logEvent('billing.upgrade_pro', $tenant->id, null, [
            'session_id' => $session->id ?? null,
            'via' => 'webhook',
        ]);
    }
}
