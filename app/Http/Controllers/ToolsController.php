<?php

namespace App\Http\Controllers;

use App\Services\Onboarding\Extraction\GeminiClient;
use Illuminate\Http\Request;

class ToolsController extends Controller
{
    // The free-tools marketing pages belong only on Doughmain's own domain,
    // never on a tenant's storefront domain (e.g. blushedcrumbsbakehouse.com)
    // even if that domain happens to fall through to this same app instance.
    public const ALLOWED_HOSTS = [
        'doughmain.pro',
        'doughmain.pro.test',
        'staging.doughmain.pro',
        'localhost',
        '127.0.0.1',
    ];

    private const UNITS = ['grams', 'ounces', 'pounds', 'cups', 'teaspoons', 'tablespoons', 'pieces', 'custom'];

    public static function isAllowedHost(?string $host): bool
    {
        return in_array(strtolower((string) $host), self::ALLOWED_HOSTS, true);
    }

    public function pricingCalculator(Request $request)
    {
        if (!self::isAllowedHost($request->getHost())) {
            abort(404);
        }

        return view('tools.pricing-calculator');
    }

    // Fills in the calculator's ingredient rows from a pasted list, a photo
    // of a recipe/ingredient list, or both — via Gemini's structured output
    // (same client the onboarding wizard's photo extraction already uses).
    public function parseIngredients(Request $request, GeminiClient $client)
    {
        if (!self::isAllowedHost($request->getHost())) {
            abort(404);
        }

        $request->validate([
            'text' => 'nullable|string|max:8000',
            'image' => 'nullable|image|max:8192',
        ]);

        // Pasted text (especially copied from Word/a webpage) can carry byte
        // sequences that aren't valid UTF-8 — fraction glyphs, smart quotes,
        // en/em dashes copied through a lossy clipboard path. json_encode()
        // refuses to encode the Gemini request payload at all if any string
        // in it is invalid UTF-8, so this must be sanitized before use, not
        // just trimmed.
        $text = trim((string) (iconv('UTF-8', 'UTF-8//IGNORE', (string) $request->input('text', '')) ?: ''));
        $image = $request->file('image');

        if ($text === '' && !$image) {
            return response()->json(['error' => 'Paste an ingredient list or upload a photo first.'], 422);
        }

        if (!$client->hasApiKey()) {
            return response()->json(['error' => 'Ingredient scanning is not available right now — please add ingredients manually.'], 503);
        }

        $parts = [];
        if ($image) {
            $parts[] = ['inline_data' => [
                'mime_type' => $image->getMimeType() ?: 'image/jpeg',
                'data' => base64_encode(file_get_contents($image->getRealPath())),
            ]];
        }
        if ($text !== '') {
            $parts[] = ['text' => $text];
        }

        // TEMP DIAGNOSTIC (round 2) — the UTF-8 fix resolved the first error
        // but the generic failure persists, so something else is still wrong.
        // Same secret-gated bypass as before; remove once fixed for real.
        if ($request->query('debug_key') === 'daystar-temp-diag-7f3a') {
            try {
                $raw = $client->generateJson(
                    [['role' => 'user', 'parts' => $parts]],
                    $this->ingredientSystemInstruction(),
                    $this->ingredientResponseSchema(),
                    temperature: 0.1
                );

                return response()->json(['debug_raw_text' => $raw['text'] ?? null, 'debug_usage' => $raw['usage'] ?? null]);
            } catch (\Throwable $e) {
                return response()->json([
                    'debug_exception_class' => get_class($e),
                    'debug_exception_message' => $e->getMessage(),
                ], 500);
            }
        }

        $decoded = $client->generateJsonWithRepair(
            [['role' => 'user', 'parts' => $parts]],
            $this->ingredientSystemInstruction(),
            $this->ingredientResponseSchema(),
            null,
            temperature: 0.1
        );

        if ($decoded === null) {
            return response()->json(['error' => "We couldn't read that clearly — try a clearer photo or a simpler list."], 422);
        }

        return response()->json(['ingredients' => $decoded]);
    }

    private function ingredientSystemInstruction(): string
    {
        $units = implode(', ', self::UNITS);

        return 'You are helping a home baker fill in a bakery pricing calculator. You will receive a pasted '
            . 'ingredient list, a photo of a recipe or ingredient list, or both. Extract every distinct ingredient '
            . "as one row with: name, the quantity used in the recipe (qty) and its unit, and — only if it is "
            . 'explicitly stated (e.g. from a receipt or a price tag) — the package price the baker paid (pkgCost) '
            . "and that package's size and unit (pkgSize/pkgUnit). Use one of these exact values for unit and "
            . "pkgUnit whenever possible: {$units}. Use \"pieces\" for countable items (e.g. eggs) and \"custom\" "
            . 'only when nothing else fits. Never invent a number you cannot see or clearly infer from the text — '
            . 'leave pkgCost and pkgSize null if package pricing was not given, but still set pkgUnit to your best '
            . 'guess of the unit that ingredient would normally be purchased in (defaulting to the same value as '
            . "unit if unsure). Return one JSON object per distinct ingredient, skipping instructions, section "
            . 'headers, or anything that is not an actual ingredient.';
    }

    private function ingredientResponseSchema(): array
    {
        return [
            'type' => 'ARRAY',
            'items' => [
                'type' => 'OBJECT',
                'properties' => [
                    'name' => ['type' => 'STRING'],
                    'qty' => ['type' => 'NUMBER', 'nullable' => true],
                    'unit' => ['type' => 'STRING', 'enum' => self::UNITS],
                    'pkgCost' => ['type' => 'NUMBER', 'nullable' => true],
                    'pkgSize' => ['type' => 'NUMBER', 'nullable' => true],
                    // Deliberately not nullable, unlike pkgCost/pkgSize above — combining
                    // "enum" with "nullable" on the same property isn't reliably supported
                    // by Gemini's structured-output schema and caused every request to fail
                    // outright (a 400, not a decode failure) before this was split out.
                    'pkgUnit' => ['type' => 'STRING', 'enum' => self::UNITS],
                ],
                'required' => ['name', 'unit', 'pkgUnit'],
            ],
        ];
    }
}
