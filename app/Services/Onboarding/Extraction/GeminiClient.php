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
                // Without this, a multi-image batch's combined JSON output can
                // silently exceed the model's own default cap and get cut off
                // mid-array — valid-looking JSON that's simply truncated, which
                // fails to parse (and fails the repair retry identically, since
                // the retry hits the same cap). Generous on purpose: a 6-image
                // batch with detailed alt_text/labels/category per image, or a
                // full synthesis copy pass, both fit comfortably under this.
                'maxOutputTokens' => (int) config('onboarding.gemini_max_output_tokens', 8192),
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

    /**
     * generateJson() plus the "one repair retry" JSON-safety layer: if the
     * response can't be parsed (or its top-level array length doesn't match
     * $expectedCount, when given), asks Gemini once to fix its own broken
     * output before giving up. Shared by every extraction/synthesis caller
     * so this policy lives in exactly one place.
     *
     * @return array|null decoded JSON array, or null if unrecoverable after the repair attempt
     */
    public function generateJsonWithRepair(array $contents, string $systemInstruction, array $responseSchema, ?int $expectedCount = null, ?string $model = null, float $temperature = 0.2): ?array
    {
        try {
            $raw = $this->generateJson($contents, $systemInstruction, $responseSchema, $model, $temperature);
        } catch (\Throwable $e) {
            Log::warning('Gemini call failed.', ['message' => $e->getMessage()]);

            return null;
        }

        $decoded = $this->tryDecode($raw['text'] ?? null, $expectedCount);
        if ($decoded !== null) {
            return $decoded;
        }

        Log::warning('Gemini primary response failed to decode — attempting one repair call.', ['expected_count' => $expectedCount]);

        try {
            $repairPrompt = 'The following was supposed to be valid JSON matching the required schema but failed to '
                . "parse or didn't match the expected shape. Return ONLY corrected valid JSON, nothing else.\n\n"
                . ($raw['text'] ?? '(empty response)');

            $repaired = $this->generateJson(
                [['role' => 'user', 'parts' => [['text' => $repairPrompt]]]],
                $systemInstruction,
                $responseSchema,
                $model,
                $temperature
            );

            return $this->tryDecode($repaired['text'] ?? null, $expectedCount);
        } catch (\Throwable $e) {
            Log::warning('Gemini repair retry failed.', ['message' => $e->getMessage()]);

            return null;
        }
    }

    private function tryDecode(?string $text, ?int $expectedCount): ?array
    {
        if (!$text) {
            Log::warning('Gemini call returned no text to decode.');

            return null;
        }

        $clean = preg_replace('/^```(?:json)?\s*/i', '', trim($text));
        $clean = preg_replace('/\s*```$/', '', $clean ?? '');
        $decoded = json_decode($clean ?? '', true);

        if (!is_array($decoded)) {
            // Deliberately logs the raw text — this is the one place that can
            // actually explain a "couldn't be parsed" fallback after the fact,
            // since GeminiExtractionService's local_only reason string alone
            // doesn't say WHY (empty response? malformed JSON? wrong shape?).
            Log::warning('Gemini response was not valid JSON.', [
                'json_error' => json_last_error_msg(),
                'expected_count' => $expectedCount,
                'text' => $text,
            ]);

            return null;
        }

        if ($expectedCount !== null && count($decoded) !== $expectedCount) {
            Log::warning('Gemini response array length did not match the number of items sent.', [
                'expected_count' => $expectedCount,
                'actual_count' => count($decoded),
                'text' => $text,
            ]);

            return null;
        }

        return $decoded;
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
