<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Talks to the Laravel Forge API to request an SSL certificate for a newly
 * verified custom domain. This is what removes the manual "add domain +
 * click Let's Encrypt" steps we had to do by hand in the Forge dashboard.
 *
 * Requires FORGE_API_TOKEN, FORGE_SERVER_ID, FORGE_SITE_ID to be set — if
 * they're not configured, methods no-op (log + return false) rather than
 * throwing, so custom domain verification itself never breaks because of
 * missing Forge credentials.
 */
class ForgeService
{
    private const BASE_URL = 'https://forge.laravel.com/api/v1';

    /**
     * Request a Let's Encrypt certificate covering the given domain and its
     * www. variant. This is the step that actually makes Forge recognize
     * and route the domain — there is no separate "add domain" API call.
     */
    public function requestCertificateFor(string $domain): bool
    {
        $token = config('services.forge.token');
        $serverId = config('services.forge.server_id');
        $siteId = config('services.forge.site_id');

        if (!$token || !$serverId || !$siteId) {
            Log::warning('Forge API not configured — skipping automatic SSL provisioning for custom domain.', [
                'domain' => $domain,
            ]);

            return false;
        }

        $domains = [$domain, 'www.' . $domain];

        try {
            $response = Http::withToken($token)
                ->timeout(30)
                ->post(self::BASE_URL . "/servers/{$serverId}/sites/{$siteId}/certificates/letsencrypt", [
                    'domains' => $domains,
                ]);

            if ($response->successful()) {
                return true;
            }

            Log::error('Forge certificate request failed for custom domain.', [
                'domain' => $domain,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        } catch (\Throwable $e) {
            Log::error('Forge certificate request threw an exception.', [
                'domain' => $domain,
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
