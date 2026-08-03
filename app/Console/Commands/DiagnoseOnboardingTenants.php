<?php

namespace App\Console\Commands;

use App\Models\Onboarding\OnboardingDraft;
use App\Models\Onboarding\OnboardingFile;
use App\Models\Tenant;
use Illuminate\Console\Command;

/**
 * Read-only diagnostic for investigating tenants whose AI-generated site
 * didn't land well (mismatched theme, missing/misfit images) — dumps their
 * theme, gallery, and onboarding draft/file data to a JSON file under
 * public/ so it can be fetched over HTTP and inspected, since Forge's
 * site-command `output` field is unreliable for anything worth reading.
 * Never writes to the database.
 */
class DiagnoseOnboardingTenants extends Command
{
    protected $signature = 'onboarding:diagnose-tenants {names* : Tenant name search terms (partial match, e.g. "Bustos" "Stevie")}';

    protected $description = 'Read-only diagnostic: dumps theme, gallery, and onboarding draft/file data for tenants matching the given name(s) to public/_debug_tenant_diag.json';

    public function handle(): int
    {
        $result = [];

        foreach ($this->argument('names') as $needle) {
            foreach (Tenant::where('name', 'like', "%{$needle}%")->get() as $tenant) {
                $draft = OnboardingDraft::where('tenant_id', $tenant->id)->orderByDesc('id')->first();
                $files = $draft ? OnboardingFile::where('draft_id', $draft->id)->get() : collect();
                $galleryCount = is_array($tenant->gallery_images) ? count($tenant->gallery_images) : 0;

                $result[] = [
                    'tenant_id' => $tenant->id,
                    'name' => $tenant->name,
                    'slug' => $tenant->slug,
                    'theme_id' => $tenant->theme_id,
                    'onboarding_completed' => $tenant->onboarding_completed,
                    'created_at' => optional($tenant->created_at)->toDateTimeString(),
                    'gallery_images_count' => $galleryCount,
                    'site_content_hero_bg_url' => data_get($tenant->site_content, 'hero_bg_url'),
                    'site_content_about_bio' => data_get($tenant->site_content, 'about_bio'),
                    'draft' => $draft ? [
                        'id' => $draft->id,
                        'status' => $draft->status,
                        'theme_id' => $draft->theme_id,
                        'confidence_overall' => (string) $draft->confidence_overall,
                        'basics' => $draft->basics,
                        'proposed_content_keys' => is_array($draft->proposed_content) ? array_keys($draft->proposed_content) : null,
                        'proposed_content_style_hints' => data_get($draft->proposed_content, 'style')
                            ?? data_get($draft->proposed_content, 'vibe')
                            ?? data_get($draft->proposed_content, 'theme_reasoning'),
                        'model_versions' => $draft->model_versions,
                        'imported_at' => optional($draft->imported_at)->toDateTimeString(),
                    ] : null,
                    'files' => $files->map(fn ($f) => [
                        'kind' => $f->kind,
                        'width' => $f->width,
                        'height' => $f->height,
                        'quality_score' => (string) $f->quality_score,
                        'is_hero_candidate' => $f->is_hero_candidate,
                        'status' => $f->status,
                        'ai_labels' => is_array($f->ai_labels) ? array_slice($f->ai_labels, 0, 5) : $f->ai_labels,
                    ])->values(),
                ];
            }
        }

        $path = public_path('_debug_tenant_diag.json');
        file_put_contents($path, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $this->info('Wrote ' . count($result) . ' tenant record(s) to ' . $path);

        return self::SUCCESS;
    }
}
