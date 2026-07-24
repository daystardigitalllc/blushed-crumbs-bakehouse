<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SocialImporterService
{
    /**
     * Import public images, description, and branding from Instagram or Facebook URL.
     *
     * @param string $url
     * @return array
     */
    public function importFromSocialUrl(string $url): array
    {
        $url = trim($url);
        if (empty($url)) {
            return ['success' => false, 'message' => 'URL is empty.', 'images' => [], 'about' => ''];
        }

        if (!str_starts_with($url, 'http://') && !str_starts_with($url, 'https://')) {
            $url = 'https://' . $url;
        }

        try {
            $response = Http::withoutVerifying()->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept-Language' => 'en-US,en;q=0.9',
            ])->timeout(3)->get($url);

            if (!$response->successful()) {
                Log::info('Social import HTTP non-200 for ' . $url . ' - using fallback assets');
                return [
                    'success' => true,
                    'url' => $url,
                    'images' => [
                        asset('images/IMG_8042.jpg'),
                        asset('images/IMG_8084.jpg'),
                        asset('images/IMG_8117.jpg'),
                        asset('images/bento_cake_mission.jpg'),
                    ],
                    'about' => '',
                ];
            }

            $html = $response->body();
            $images = [];
            $about = '';

            // Extract OpenGraph Image
            if (preg_match_all('/<meta[^>]+property=["\']og:image["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $matches)) {
                foreach ($matches[1] as $img) {
                    $img = html_entity_decode($img);
                    if ($img && !in_array($img, $images)) {
                        $images[] = $img;
                    }
                }
            }

            // Reverse match order for <meta content="..." property="og:image">
            if (preg_match_all('/<meta[^>]+content=["\']([^"\']+)["\'][^>]+property=["\']og:image["\']/i', $html, $matches2)) {
                foreach ($matches2[1] as $img) {
                    $img = html_entity_decode($img);
                    if ($img && !in_array($img, $images)) {
                        $images[] = $img;
                    }
                }
            }

            // Extract Facebook/Instagram CDN image links from page HTML
            if (preg_match_all('/(https?:\/\/[^"\'>\s]+\.(?:fbcdn\.net|cdninstagram\.com)[^"\'>\s]*)/i', $html, $cdnMatches)) {
                foreach ($cdnMatches[1] as $img) {
                    $img = html_entity_decode($img);
                    if ($img && !in_array($img, $images)) {
                        $images[] = $img;
                    }
                }
            }

            // Extract OpenGraph Description (About text)
            if (preg_match('/<meta[^>]+property=["\']og:description["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $descMatch)) {
                $about = html_entity_decode($descMatch[1]);
            } elseif (preg_match('/<meta[^>]+content=["\']([^"\']+)["\'][^>]+property=["\']og:description["\']/i', $html, $descMatch2)) {
                $about = html_entity_decode($descMatch2[1]);
            }

            // If Meta blocks scrapers or returns 0 images, provide curated starter photos
            if (empty($images)) {
                $images = [
                    asset('images/IMG_8042.jpg'),
                    asset('images/IMG_8084.jpg'),
                    asset('images/IMG_8117.jpg'),
                    asset('images/bento_cake_mission.jpg'),
                ];
            }

            return [
                'success' => true,
                'url' => $url,
                'images' => array_values(array_unique($images)),
                'about' => trim($about),
            ];
        } catch (\Exception $e) {
            Log::warning('Social import error for ' . $url . ': ' . $e->getMessage());
            return [
                'success' => true,
                'url' => $url,
                'images' => [
                    asset('images/IMG_8042.jpg'),
                    asset('images/IMG_8084.jpg'),
                    asset('images/IMG_8117.jpg'),
                    asset('images/bento_cake_mission.jpg'),
                ],
                'about' => '',
            ];
        }
    }
}
