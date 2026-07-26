<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Stancl\Tenancy\Database\Models\Domain;

class BackfillTenantDomains extends Command
{
    protected $signature = 'tenants:backfill-domains {--dry-run : Report what would be created without writing anything}';

    protected $description = 'Populate the new domains table from each tenant\'s existing subdomain/custom_domain columns (stancl/tenancy Phase 1 setup)';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $this->info($dryRun ? 'DRY RUN — no domain rows will be created.' : 'LIVE RUN — domain rows will be created.');

        $created = 0;
        $skippedExisting = 0;

        Tenant::with('brand')->chunkById(100, function ($tenants) use ($dryRun, &$created, &$skippedExisting) {
            foreach ($tenants as $tenant) {
                $candidates = [];

                if (!empty($tenant->subdomain)) {
                    $brandDomain = $tenant->brand->domain ?? 'doughmain.pro';
                    $candidates[] = "{$tenant->subdomain}.{$brandDomain}";
                }

                if (!empty($tenant->custom_domain)) {
                    $candidates[] = $tenant->custom_domain;
                }

                foreach (array_unique($candidates) as $domainName) {
                    $exists = Domain::where('domain', $domainName)->exists();

                    if ($exists) {
                        $skippedExisting++;
                        continue;
                    }

                    $this->line("  [tenant #{$tenant->id} \"{$tenant->name}\"] {$domainName}");
                    $created++;

                    if (!$dryRun) {
                        Domain::create([
                            'domain' => $domainName,
                            'tenant_id' => $tenant->id,
                        ]);
                    }
                }
            }
        });

        $this->info("Done. Domain rows " . ($dryRun ? 'to create' : 'created') . ": {$created}. Already existed (skipped): {$skippedExisting}.");

        return self::SUCCESS;
    }
}
