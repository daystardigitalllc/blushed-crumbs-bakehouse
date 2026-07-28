<?php

namespace App\Services\Onboarding\Extraction;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Shared Gemini HTTP concerns for the extraction pipeline: auth, retry, and
 * token-usage logging. Deliberately does NOT call Http::withoutVerifying()
 * the way AiContentService does (left alone per the plan's "Known debt") —
 * TLS verification stays on; config('services.gemini.verify_tls') exists
 * only as an emergency escape hatch, default true.
 */
class GeminiClient
{
    private const BASE_URL = 'https://generativelanguage.googleapis.com/v1beta/models';

    private string $apiKey;

    public function __construct(?string $apiKey = null)
    {
        $this->apiKey = $apiKey ?? (string) (config('services.gemini.key') ?: '');
    }

    public function hasApiKey(): bool
    {
        return $this->apiKey !== '';
    }

    /**
     * @param array $contents Gemini "contents" array (parts, inline data, etc.)
     * @return array{text:?string,usage:array}
     * @throws \RuntimeException on a non-retryable failure or exhausted retries
     */
    public function generateJson(array $contents, string $systemInstruction, array $responseSchema, ?string $model = null, float $temperature = 0.2): array
    {
        $model ??= (string) config('services.gemini.extraction_model', 'gemini-3.5-flash');
        $url = self::BASE_URL . "/{$model}:generateContent?key={$this->apiKey}";

        $payload = [
            'systemInstruction' => ['parts' => [['text' => $systemInstruction]]],
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => $temperature,
                'responseMimeType' => 'application/json',
                'responseSchema' => $responseSchema,
            ],
        ];

        $response = $this->postWithRetry($url, $payload);

        $text = $response->json('candidates.0.content.parts.0.text');
        $usage = $response->json('usageMetadata', []);

        if (!empty($usage)) {
            Log::info('Gemini extraction call usage.', $usage);
        }

        return ['text' => $text, 'usage' => $usage ?? []];
    }

    private function postWithRetry(string $url, array $payload, int $attempts = 3): Response
    {
        $lastResponse = null;

        for ($attempt = 1; $attempt <= $attempts; $attempt++) {
            try {
                $request = Http::withHeaders(['Content-Type' => 'application/json'])->timeout(60);

                if (!(bool) config('services.gemini.verify_tls', true)) {
                    $request = $request->withoutVerifying();
                }

                $response = $request->post($url, $payload);

                if ($response->successful()) {
                    return $response;
                }

                $lastResponse = $response;

                // Only retry transient failures — a 4xx (bad request, bad key,
                // schema rejected) will fail identically on retry.
                if ($response->status() < 500 && $response->status() !== 429) {
                    break;
                }
            } catch (ConnectionException $e) {
                Log::warning('Gemini extraction request threw a connection exception.', [
                    'attempt' => $attempt,
                    'message' => $e->getMessage(),
                ]);
            }

            if ($attempt < $attempts) {
                // Small backoff — this runs inside a queue worker, not a web request.
                usleep(200_000 * $attempt);
            }
        }

        throw new \RuntimeException(
            'Gemini API request failed after ' . $attempts . ' attempts. Last status: '
            . ($lastResponse?->status() ?? 'none (connection error)')
        );
    }
}
