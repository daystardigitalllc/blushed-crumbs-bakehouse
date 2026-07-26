<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Looks up a real stock photo on Unsplash for a short search phrase (e.g.
 * "rustic sourdough bakery interior"). Used to fill in background imagery
 * for AI-generated sites — Gemini decides *what* to search for based on the
 * bakery's style/content, this just fetches the actual photo.
 *
 * Fails soft: returns null on any error (missing key, no results, API
 * down) so a stock-photo hiccup never breaks website generation — the
 * section just falls back to its existing gradient background.
 */
class UnsplashService
{
    private const BASE_URL = 'https://api.unsplash.com';

    /**
     * @return array{url: string, credit_name: string, credit_url: string}|null
     */
    public function searchPhoto(string $query): ?array
    {
        $accessKey = config('services.unsplash.access_key');

        if (!$accessKey || trim($query) === '') {
            return null;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Client-ID ' . $accessKey,
            ])->timeout(10)->get(self::BASE_URL . '/search/photos', [
                'query' => $query,
                'per_page' => 1,
                'orientation' => 'landscape',
                'content_filter' => 'high',
            ]);

            if (!$response->successful()) {
                Log::warning('Unsplash search failed.', [
                    'query' => $query,
                    'status' => $response->status(),
                ]);

                return null;
            }

            $photo = $response->json('results.0');
            if (!$photo) {
                return null;
            }

            // Unsplash API guidelines require pinging this when a photo is
            // actually used, separate from the search request itself.
            if (!empty($photo['links']['download_location'])) {
                Http::withHeaders(['Authorization' => 'Client-ID ' . $accessKey])
                    ->timeout(5)
                    ->get($photo['links']['download_location']);
            }

            return [
                'url' => $photo['urls']['regular'] ?? $photo['urls']['full'] ?? '',
                'credit_name' => $photo['user']['name'] ?? 'Unsplash',
                'credit_url' => ($photo['user']['links']['html'] ?? 'https://unsplash.com') . '?utm_source=doughmain&utm_medium=referral',
            ];
        } catch (\Throwable $e) {
            Log::error('Unsplash search threw an exception.', [
                'query' => $query,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
