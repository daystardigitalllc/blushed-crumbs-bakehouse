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

        $text = trim((string) $request->input('text', ''));
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
            . 'leave pkgCost, pkgSize, and pkgUnit null if package pricing was not given. Return one JSON object '
            . 'per distinct ingredient, skipping instructions, section headers, or anything that is not an actual '
            . 'ingredient.';
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
                    'pkgUnit' => ['type' => 'STRING', 'enum' => self::UNITS, 'nullable' => true],
                ],
                'required' => ['name', 'unit'],
            ],
        ];
    }
}
