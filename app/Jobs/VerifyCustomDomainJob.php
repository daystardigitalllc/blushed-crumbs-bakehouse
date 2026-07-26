<?php

namespace App\Jobs;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Checks whether a baker has actually pointed the custom domain they entered
 * at us, and that they own it (proven via a TXT record carrying a token we
 * generated). Runs once per "Verify" click rather than retrying forever, so
 * the baker gets a clear pass/fail instead of a job silently looping.
 */
class VerifyCustomDomainJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 20;

    public function __construct(private int $tenantId, private string $domainAtQueueTime)
    {
    }

    public function handle(): void
    {
        $tenant = Tenant::find($this->tenantId);
        if (!$tenant || !$tenant->custom_domain) {
            return;
        }

        // The domain may have changed (or been cleared) between when this
        // job was queued and when it actually runs — don't act on stale input.
        if ($tenant->custom_domain !== $this->domainAtQueueTime) {
            return;
        }

        $domain = $tenant->custom_domain;
        $token = $tenant->custom_domain_token;

        $tenant->custom_domain_last_checked_at = now();

        if (!$token || !$this->ownershipTokenPresent($domain, $token)) {
            $tenant->custom_domain_status = 'failed';
            $tenant->custom_domain_last_error = 'Ownership TXT record not found yet. Add the TXT record shown in your dashboard, then try again — DNS changes can take a few minutes to a few hours to take effect.';
            $tenant->save();

            return;
        }

        if (!$this->domainResolves($domain)) {
            $tenant->custom_domain_status = 'failed';
            $tenant->custom_domain_last_error = 'Domain ownership confirmed, but the domain doesn\'t resolve yet. Make sure your A or CNAME record is set, then try again.';
            $tenant->save();

            return;
        }

        $tenant->custom_domain_status = 'verified';
        $tenant->custom_domain_verified_at = now();
        $tenant->custom_domain_last_error = null;
        $tenant->save();

        // Make the domain actually routable: this is what ResolveTenant reads.
        $tenant->domains()->firstOrCreate(['domain' => strtolower($domain)]);
    }

    private function ownershipTokenPresent(string $domain, string $token): bool
    {
        $expected = 'doughmain-verify=' . $token;
        $records = @dns_get_record('_doughmain-verify.' . $domain, DNS_TXT);

        if (!is_array($records)) {
            return false;
        }

        foreach ($records as $record) {
            $txt = $record['txt'] ?? '';
            if (trim($txt) === $expected) {
                return true;
            }
        }

        return false;
    }

    private function domainResolves(string $domain): bool
    {
        $aRecords = @dns_get_record($domain, DNS_A);
        if (is_array($aRecords) && count($aRecords) > 0) {
            return true;
        }

        $cnameRecords = @dns_get_record($domain, DNS_CNAME);

        return is_array($cnameRecords) && count($cnameRecords) > 0;
    }
}
