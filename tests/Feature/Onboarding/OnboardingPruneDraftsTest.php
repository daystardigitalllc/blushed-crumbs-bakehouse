<?php

namespace Tests\Feature\Onboarding;

use App\Models\AuditLog;
use App\Models\Onboarding\OnboardingDraft;
use App\Models\Onboarding\OnboardingFile;
use App\Models\Tenant;
use App\Services\Onboarding\TenantMediaPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * The Phase 9 two-tier retention drills from the plan: a 49h-inactive
 * incomplete draft is purged (row + files + AuditLog), a 47h one is left
 * alone, touching a draft resets its clock, an 'importing' draft is never
 * purged regardless of age, and an imported draft loses its originals at 7
 * days but keeps its row.
 */
class OnboardingPruneDraftsTest extends TestCase
{
    use RefreshDatabase;

    private function makeTenant(): Tenant
    {
        return Tenant::create([
            'name' => 'Prune Test Bakery',
            'slug' => 'prune-test-' . Str::random(8),
            'owner_name' => 'Test Owner',
            'email' => Str::random(8) . '@example.com',
            'plan_tier' => 'standard',
            'theme_id' => 'rustic_kitchen',
            'is_active' => true,
        ]);
    }

    private function writeDraftFile(Tenant $tenant, OnboardingDraft $draft): string
    {
        $dir = TenantMediaPath::draftOriginalsDir($tenant->id, $draft->id);
        TenantMediaPath::ensureDir($dir);
        $path = $dir . '/original.jpg';
        file_put_contents($path, 'fake-bytes');

        return $path;
    }

    public function test_incomplete_draft_past_48h_is_purged_with_files_and_audit_log()
    {
        $tenant = $this->makeTenant();
        $draft = OnboardingDraft::create([
            'tenant_id' => $tenant->id,
            'version' => 1,
            'status' => 'ready_for_review',
            'last_activity_at' => now()->subHours(49),
        ]);
        $path = $this->writeDraftFile($tenant, $draft);

        $this->artisan('onboarding:prune-drafts')->assertSuccessful();

        $this->assertFileDoesNotExist($path);
        $this->assertNull(OnboardingDraft::find($draft->id));
        $this->assertSame(
            1,
            AuditLog::where('event_type', 'onboarding.draft.purged')->where('tenant_id', $tenant->id)->count()
        );
    }

    public function test_incomplete_draft_at_47h_is_left_alone()
    {
        $tenant = $this->makeTenant();
        $draft = OnboardingDraft::create([
            'tenant_id' => $tenant->id,
            'version' => 1,
            'status' => 'ready_for_review',
            'last_activity_at' => now()->subHours(47),
        ]);

        $this->artisan('onboarding:prune-drafts')->assertSuccessful();

        $this->assertNotNull(OnboardingDraft::find($draft->id));
    }

    public function test_touching_last_activity_at_resets_the_clock()
    {
        $tenant = $this->makeTenant();
        $draft = OnboardingDraft::create([
            'tenant_id' => $tenant->id,
            'version' => 1,
            'status' => 'ready_for_review',
            'last_activity_at' => now()->subHours(60), // would be purged...
        ]);
        $draft->last_activity_at = now(); // ...but they came back
        $draft->save();

        $this->artisan('onboarding:prune-drafts')->assertSuccessful();

        $this->assertNotNull(OnboardingDraft::find($draft->id));
    }

    public function test_importing_draft_is_never_purged_regardless_of_age()
    {
        $tenant = $this->makeTenant();
        $draft = OnboardingDraft::create([
            'tenant_id' => $tenant->id,
            'version' => 1,
            'status' => 'importing',
            'last_activity_at' => now()->subDays(30),
        ]);

        $this->artisan('onboarding:prune-drafts')->assertSuccessful();

        $this->assertNotNull(OnboardingDraft::find($draft->id));
    }

    public function test_dry_run_reports_but_does_not_delete()
    {
        $tenant = $this->makeTenant();
        $draft = OnboardingDraft::create([
            'tenant_id' => $tenant->id,
            'version' => 1,
            'status' => 'ready_for_review',
            'last_activity_at' => now()->subHours(49),
        ]);
        $path = $this->writeDraftFile($tenant, $draft);

        $this->artisan('onboarding:prune-drafts --dry-run')->assertSuccessful();

        $this->assertFileExists($path);
        $this->assertNotNull(OnboardingDraft::find($draft->id));
    }

    public function test_imported_draft_loses_originals_at_7_days_but_keeps_its_row()
    {
        $tenant = $this->makeTenant();
        $draft = OnboardingDraft::create([
            'tenant_id' => $tenant->id,
            'version' => 1,
            'status' => 'imported',
            'imported_at' => now()->subDays(8),
        ]);
        $path = $this->writeDraftFile($tenant, $draft);

        $this->artisan('onboarding:prune-drafts')->assertSuccessful();

        $this->assertFileDoesNotExist($path);
        $this->assertNotNull(OnboardingDraft::find($draft->id));
        $this->assertSame('imported', $draft->fresh()->status);
    }

    public function test_imported_draft_under_7_days_keeps_its_originals()
    {
        $tenant = $this->makeTenant();
        $draft = OnboardingDraft::create([
            'tenant_id' => $tenant->id,
            'version' => 1,
            'status' => 'imported',
            'imported_at' => now()->subDays(6),
        ]);
        $path = $this->writeDraftFile($tenant, $draft);

        $this->artisan('onboarding:prune-drafts')->assertSuccessful();

        $this->assertFileExists($path);
    }
}
