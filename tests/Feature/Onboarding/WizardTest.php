<?php

namespace Tests\Feature\Onboarding;

use App\Jobs\Onboarding\ImportDraftJob;
use App\Livewire\Onboarding\ActivityFeed;
use App\Livewire\Onboarding\FileGrid;
use App\Livewire\Onboarding\ProgressBar;
use App\Livewire\Onboarding\ReviewPanel;
use App\Livewire\Onboarding\Wizard;
use App\Models\Onboarding\OnboardingDraft;
use App\Models\Onboarding\OnboardingDraftItem;
use App\Models\Onboarding\OnboardingEvent;
use App\Models\Onboarding\OnboardingFile;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Covers Phase 8's security-critical requirements from the plan (an
 * unlocked draft ID would be an IDOR into another tenant's draft — every
 * component must independently re-verify tenant ownership) and the
 * "no step advances on failure" fix for the legacy wizard's silent data
 * loss: steps only ever move on a verified backend state, never optimistically.
 */
class WizardTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(array $tenantOverrides = []): User
    {
        $tenant = Tenant::create(array_merge([
            'name' => 'Wizard Test Bakery',
            'slug' => 'wizard-test-' . Str::random(8),
            'subdomain' => 'wizard-test-' . Str::random(8),
            'owner_name' => 'Test Owner',
            'email' => Str::random(8) . '@example.com',
            'plan_tier' => 'free',
            'theme_id' => 'rustic_kitchen',
            'is_active' => true,
        ], $tenantOverrides));

        return User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Test Owner',
            'email' => Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'owner',
        ]);
    }

    public function test_mount_without_a_draft_creates_one_and_redirects_to_it()
    {
        $user = $this->makeUser();

        // A real HTTP request rather than Livewire::test() — mount()'s
        // create-and-redirect path is the component's routable-page
        // bootstrap, not an isolated component interaction.
        $this->actingAs($user)
            ->get(route('onboarding.v2.wizard'))
            ->assertRedirect();

        $this->assertSame(1, OnboardingDraft::where('tenant_id', $user->tenant_id)->count());
    }

    public function test_mount_resumes_an_existing_non_imported_draft()
    {
        $user = $this->makeUser();
        $draft = OnboardingDraft::create(['tenant_id' => $user->tenant_id, 'version' => 1, 'status' => 'collecting']);

        Livewire::actingAs($user)
            ->test(Wizard::class, ['draft' => $draft])
            ->assertSet('draftId', $draft->id)
            ->assertSet('step', 'basics');
    }

    public function test_accessing_another_tenants_draft_is_forbidden()
    {
        $owner = $this->makeUser();
        $intruder = $this->makeUser();
        $draft = OnboardingDraft::create(['tenant_id' => $owner->tenant_id, 'version' => 1, 'status' => 'collecting']);

        // Livewire's test harness converts an abort() during mount into a
        // captured response rather than a catchable exception — assert on
        // the resulting status, not a thrown HttpException.
        Livewire::actingAs($intruder)
            ->test(Wizard::class, ['draft' => $draft])
            ->assertStatus(403);
    }

    public function test_child_components_reject_another_tenants_draft_id()
    {
        $owner = $this->makeUser();
        $intruder = $this->makeUser();
        $draft = OnboardingDraft::create(['tenant_id' => $owner->tenant_id, 'version' => 1, 'status' => 'collecting']);

        foreach ([ProgressBar::class, ActivityFeed::class, FileGrid::class, ReviewPanel::class] as $component) {
            Livewire::actingAs($intruder)
                ->test($component, ['draftId' => $draft->id])
                ->assertStatus(403);
        }
    }

    public function test_save_basics_persists_and_advances_to_upload_step()
    {
        $user = $this->makeUser();
        $draft = OnboardingDraft::create(['tenant_id' => $user->tenant_id, 'version' => 1, 'status' => 'collecting']);

        Livewire::actingAs($user)
            ->test(Wizard::class, ['draft' => $draft])
            ->set('basicsForm.business_name', 'My Bakery')
            ->set('basicsForm.hours', 'Mon-Fri 8-4')
            ->call('saveBasics')
            ->assertSet('step', 'upload');

        $this->assertSame('My Bakery', $draft->fresh()->basics['business_name']);
    }

    /**
     * Regression: storeLogo() used to call Symfony's UploadedFile::move(),
     * which only works on a file uploaded in the CURRENT HTTP request.
     * Livewire::fake() reproduces the real failure mode — the temp file
     * genuinely exists on disk as an ordinary file (not one PHP's own
     * upload-tracking recognizes), so this test would have caught the
     * production 500 if it had existed beforehand.
     */
    public function test_save_basics_stores_an_uploaded_logo()
    {
        \Illuminate\Http\UploadedFile::fake()->image('logo.png')->size(100);
        $user = $this->makeUser();
        $draft = OnboardingDraft::create(['tenant_id' => $user->tenant_id, 'version' => 1, 'status' => 'collecting']);

        Livewire::actingAs($user)
            ->test(Wizard::class, ['draft' => $draft])
            ->set('basicsForm.business_name', 'My Bakery')
            ->set('logo', \Illuminate\Http\UploadedFile::fake()->image('logo.png'))
            ->call('saveBasics')
            ->assertSet('step', 'upload')
            ->assertHasNoErrors();

        $draft->refresh();
        $this->assertNotNull($draft->logo_path);
        $this->assertFileExists($draft->logo_path);
    }

    public function test_save_basics_requires_a_business_name()
    {
        $user = $this->makeUser();
        $draft = OnboardingDraft::create(['tenant_id' => $user->tenant_id, 'version' => 1, 'status' => 'collecting']);

        Livewire::actingAs($user)
            ->test(Wizard::class, ['draft' => $draft])
            ->set('basicsForm.business_name', '')
            ->call('saveBasics')
            ->assertHasErrors(['basicsForm.business_name'])
            ->assertSet('step', 'basics'); // never advances on a failed validation
    }

    public function test_analyzing_step_only_advances_when_draft_status_actually_reads_ready_for_review()
    {
        $user = $this->makeUser();
        $draft = OnboardingDraft::create(['tenant_id' => $user->tenant_id, 'version' => 1, 'status' => 'extracting']);

        $component = Livewire::actingAs($user)
            ->test(Wizard::class, ['draft' => $draft])
            ->set('step', 'analyzing')
            ->call('checkProgress')
            ->assertSet('step', 'analyzing'); // still extracting — no premature advance

        $draft->update(['status' => 'ready_for_review']);

        $component->call('checkProgress')->assertSet('step', 'review');
    }

    public function test_analyzing_step_shows_failure_state_on_a_verified_failed_status()
    {
        $user = $this->makeUser();
        $draft = OnboardingDraft::create(['tenant_id' => $user->tenant_id, 'version' => 1, 'status' => 'failed']);

        Livewire::actingAs($user)
            ->test(Wizard::class, ['draft' => $draft])
            ->set('step', 'analyzing')
            ->call('checkProgress')
            ->assertSet('step', 'analyzing_failed');
    }

    public function test_build_site_dispatches_import_job_only_from_ready_for_review()
    {
        Bus::fake();

        $user = $this->makeUser();
        $draft = OnboardingDraft::create(['tenant_id' => $user->tenant_id, 'version' => 1, 'status' => 'ready_for_review']);

        Livewire::actingAs($user)
            ->test(Wizard::class, ['draft' => $draft])
            ->set('step', 'review')
            ->call('buildSite')
            ->assertSet('step', 'building');

        Bus::assertDispatched(ImportDraftJob::class, fn ($job) => $job->draftId === $draft->id);
    }

    public function test_build_site_refuses_when_draft_is_not_ready_for_review()
    {
        $user = $this->makeUser();
        $draft = OnboardingDraft::create(['tenant_id' => $user->tenant_id, 'version' => 1, 'status' => 'collecting']);

        Livewire::actingAs($user)
            ->test(Wizard::class, ['draft' => $draft])
            ->call('buildSite')
            ->assertStatus(403);
    }

    public function test_progress_bar_reflects_file_status_counts()
    {
        $user = $this->makeUser();
        $draft = OnboardingDraft::create(['tenant_id' => $user->tenant_id, 'version' => 1, 'status' => 'extracting']);

        OnboardingFile::create(['draft_id' => $draft->id, 'tenant_id' => $user->tenant_id, 'kind' => 'image', 'path' => '/tmp/a.jpg', 'content_hash' => Str::uuid(), 'status' => 'extracted']);
        OnboardingFile::create(['draft_id' => $draft->id, 'tenant_id' => $user->tenant_id, 'kind' => 'image', 'path' => '/tmp/b.jpg', 'content_hash' => Str::uuid(), 'status' => 'pending']);

        Livewire::actingAs($user)
            ->test(ProgressBar::class, ['draftId' => $draft->id])
            ->assertSee('50%');
    }

    public function test_activity_feed_shows_recent_events()
    {
        $user = $this->makeUser();
        $draft = OnboardingDraft::create(['tenant_id' => $user->tenant_id, 'version' => 1, 'status' => 'collecting']);
        OnboardingEvent::create(['draft_id' => $draft->id, 'tenant_id' => $user->tenant_id, 'type' => 'file_uploaded', 'message' => 'cake.jpg']);

        Livewire::actingAs($user)
            ->test(ActivityFeed::class, ['draftId' => $draft->id])
            ->assertSee('cake.jpg');
    }

    public function test_file_grid_refreshes_on_the_file_uploaded_event()
    {
        $user = $this->makeUser();
        $draft = OnboardingDraft::create(['tenant_id' => $user->tenant_id, 'version' => 1, 'status' => 'collecting']);

        $component = Livewire::actingAs($user)->test(FileGrid::class, ['draftId' => $draft->id]);
        $component->assertDontSee('cake.jpg');

        OnboardingFile::create(['draft_id' => $draft->id, 'tenant_id' => $user->tenant_id, 'original_filename' => 'cake.jpg', 'kind' => 'image', 'path' => '/tmp/a.jpg', 'content_hash' => Str::uuid(), 'status' => 'pending']);

        $component->dispatch('file-uploaded')->assertSee('cake.jpg');
    }

    public function test_review_panel_reject_and_theme_change()
    {
        $user = $this->makeUser();
        $draft = OnboardingDraft::create(['tenant_id' => $user->tenant_id, 'version' => 1, 'status' => 'ready_for_review', 'theme_id' => 'rustic_kitchen']);
        $product = OnboardingDraftItem::create([
            'draft_id' => $draft->id, 'tenant_id' => $user->tenant_id, 'type' => 'product',
            'dedupe_key' => 'chocolate-cake', 'payload_final' => ['name' => 'Chocolate Cake', 'price_min' => 45], 'status' => 'pending',
        ]);

        $component = Livewire::actingAs($user)->test(ReviewPanel::class, ['draftId' => $draft->id]);

        $component->call('rejectItem', $product->id, 'product');
        $this->assertSame('rejected', $product->fresh()->status);

        $component->call('changeTheme', 'modern_bakery');
        $this->assertSame('modern_bakery', $draft->fresh()->theme_id);

        // A theme outside the tenant's gated set (standard plan, no pro
        // selection) must be silently refused, not written through.
        $component->call('changeTheme', 'sweet_elegant');
        $this->assertSame('modern_bakery', $draft->fresh()->theme_id);
    }
}
