<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CottageFoodLawsController extends Controller
{
    public function index(Request $request)
    {
        if (!ToolsController::isAllowedHost($request->getHost())) {
            abort(404);
        }

        return view('tools.cottage-food-laws.index', [
            'states' => self::allStatesSummary(),
        ]);
    }

    public function show(Request $request, string $state)
    {
        if (!ToolsController::isAllowedHost($request->getHost())) {
            abort(404);
        }

        $data = self::stateData($state);

        if ($data === null) {
            abort(404);
        }

        return view('tools.cottage-food-laws.show', [
            'state' => $data,
            'allStates' => self::allStatesSummary(),
        ]);
    }

    /**
     * A state's full record, with defaults merged in — the only thing a
     * state entry in config/cottage_food_laws.php needs to define is what's
     * actually different for that state. See the config file's top comment
     * for how to add or update a state.
     */
    public static function stateData(string $slug): ?array
    {
        $config = config('cottage_food_laws');
        $raw = $config['states'][$slug] ?? null;

        if ($raw === null) {
            return null;
        }

        $defaults = $config['defaults'];

        return [
            'slug' => $slug,
            'name' => $raw['name'],
            'abbr' => $raw['abbr'],
            'summary' => $raw['summary'],
            'sales_limit' => $raw['sales_limit'],
            'permits' => $raw['permits'],
            'allowed_foods' => $raw['allowed_foods'] ?? $defaults['allowed_foods'],
            'prohibited_foods' => $raw['prohibited_foods'] ?? $defaults['prohibited_foods'],
            'prohibited_foods_note' => $raw['prohibited_foods_note'] ?? null,
            'labeling_requirements' => $raw['labeling_requirements'] ?? $defaults['labeling_requirements'],
            'selling_locations' => array_merge($defaults['selling_locations'], $raw['selling_restrictions'] ?? []),
            'official_source' => $raw['official_source'],
            'official_source_name' => $raw['official_source_name'],
            'last_updated' => $raw['last_updated'],
        ];
    }

    /**
     * Lightweight list of every state (name/slug/abbr/sales_limit only) for
     * the state picker, the "Compare States" feature, and internal links —
     * avoids fully merging defaults for all 50 states on every page load.
     */
    public static function allStatesSummary(): array
    {
        $states = config('cottage_food_laws.states');

        $summary = collect($states)->map(function (array $raw, string $slug) {
            return [
                'slug' => $slug,
                'name' => $raw['name'],
                'abbr' => $raw['abbr'],
                'sales_limit' => $raw['sales_limit'],
            ];
        })->values()->all();

        usort($summary, fn ($a, $b) => $a['name'] <=> $b['name']);

        return $summary;
    }
}
