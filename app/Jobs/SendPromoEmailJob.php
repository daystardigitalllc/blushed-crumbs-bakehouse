<?php

namespace App\Jobs;

use App\Mail\PromoEmail;
use App\Models\EmailCampaign;
use App\Models\EmailSubscriber;
use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * One subscriber, one send — queued individually (rather than one job that
 * loops the whole list) so a single bad address can't hold up or crash the
 * rest of a campaign, and so queue:work's own retry/backoff applies
 * per-recipient. Runs on the dedicated `emails` queue (see the `emails`
 * Forge daemon) since the only pre-existing worker only drains
 * `ingest`/`ai-import`.
 */
class SendPromoEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;
    public int $timeout = 30;

    public function __construct(
        private int $tenantId,
        private int $campaignId,
        private int $subscriberId,
    ) {
    }

    public function handle(): void
    {
        $tenant = Tenant::find($this->tenantId);
        $campaign = EmailCampaign::find($this->campaignId);
        $subscriber = EmailSubscriber::withoutGlobalScopes()->find($this->subscriberId);

        // Any of these can legitimately disappear between dispatch and
        // execution (tenant deleted, subscriber unsubscribed/removed) —
        // nothing to send to, not a failure worth retrying.
        if (!$tenant || !$campaign || !$subscriber || $subscriber->unsubscribed_at) {
            return;
        }

        Mail::to($subscriber->email)->send(new PromoEmail($tenant, $campaign, $subscriber));

        $this->markDelivered($campaign);
    }

    /**
     * Atomic increment + completion check so concurrent workers finishing
     * at the same time can't both think they're "last" and double-write
     * the final status, and a slow straggler can't reset it back to
     * 'sending' after a faster job already marked 'sent'.
     */
    private function markDelivered(EmailCampaign $campaign): void
    {
        DB::transaction(function () use ($campaign) {
            $locked = EmailCampaign::whereKey($campaign->id)->lockForUpdate()->first();
            if (!$locked) {
                return;
            }

            $locked->increment('sent_count');

            if ($locked->sent_count + $locked->failed_count >= $locked->recipient_count) {
                $locked->update([
                    'status' => $locked->sent_count > 0 ? 'sent' : 'failed',
                    'sent_at' => now(),
                ]);
            }
        });
    }

    /**
     * Called after all retries are exhausted — counts the recipient as
     * failed so the campaign still reaches a terminal status instead of
     * sitting at "sending" forever because of one bad address.
     */
    public function failed(\Throwable $e): void
    {
        report($e);

        $campaign = EmailCampaign::find($this->campaignId);
        if (!$campaign) {
            return;
        }

        DB::transaction(function () use ($campaign) {
            $locked = EmailCampaign::whereKey($campaign->id)->lockForUpdate()->first();
            if (!$locked) {
                return;
            }

            $locked->increment('failed_count');

            if ($locked->sent_count + $locked->failed_count >= $locked->recipient_count) {
                $locked->update([
                    'status' => $locked->sent_count > 0 ? 'sent' : 'failed',
                    'sent_at' => now(),
                ]);
            }
        });
    }
}
