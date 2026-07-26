<?php

namespace App\Console\Commands;

use App\Models\GalleryItem;
use App\Models\Order;
use App\Models\Tenant;
use Illuminate\Console\Command;

class FixTenantStorageIsolation extends Command
{
    protected $signature = 'tenants:fix-storage-isolation {--dry-run : Report what would change without touching any files or records}';

    protected $description = 'Move legacy flat/shared uploaded files (inspiration photos, gallery images, logos, generic media) into per-tenant folders and update the DB records that reference them';

    private bool $dryRun = true;
    private int $moved = 0;
    private int $skippedMissing = 0;

    public function handle(): int
    {
        $this->dryRun = (bool) $this->option('dry-run');
        $this->info($this->dryRun ? 'DRY RUN — no files or records will be changed.' : 'LIVE RUN — files will be copied and records updated.');

        $this->migrateOrderInspirationFiles();
        $this->migrateTenantLogos();
        $this->migrateTenantJsonMediaPaths();
        $this->migrateGalleryItems();

        $this->info("Done. Files moved: {$this->moved}. Skipped (source missing): {$this->skippedMissing}.");

        return self::SUCCESS;
    }

    /**
     * Given a flat path like "uploads/inspiration/foo.jpg" or "uploads/foo.jpg",
     * return the new tenant-scoped relative path, or null if it's already
     * tenant-scoped / not a recognizable upload path.
     */
    private function resolveNewPath(string $path, int $tenantId, string $fallbackSubfolder): ?string
    {
        if (!preg_match('#^uploads/(?!tenants/)(?:([a-zA-Z0-9_-]+)/)?([a-zA-Z0-9._-]+\.[a-zA-Z0-9]+)$#', $path, $m)) {
            return null;
        }

        $subfolder = $m[1] !== '' ? $m[1] : $fallbackSubfolder;
        $filename = $m[2];

        return "uploads/tenants/{$tenantId}/{$subfolder}/{$filename}";
    }

    private function copyFile(string $oldRelative, string $newRelative): bool
    {
        $oldFull = public_path($oldRelative);
        $newFull = public_path($newRelative);

        if (!file_exists($oldFull)) {
            $this->warn("  source file missing, skipping: {$oldRelative}");
            $this->skippedMissing++;

            return false;
        }

        $this->line("  {$oldRelative} -> {$newRelative}");

        if (!$this->dryRun) {
            $destDir = dirname($newFull);
            if (!is_dir($destDir)) {
                mkdir($destDir, 0755, true);
            }
            copy($oldFull, $newFull);
        }

        $this->moved++;

        return true;
    }

    private function migrateOrderInspirationFiles(): void
    {
        $this->info('--- Order inspiration_files ---');

        Order::whereNotNull('inspiration_files')->chunkById(200, function ($orders) {
            foreach ($orders as $order) {
                $paths = $order->inspiration_files;
                if (!is_array($paths) || empty($paths)) {
                    continue;
                }

                $changed = false;
                $newPaths = [];

                foreach ($paths as $path) {
                    $newPath = is_string($path) ? $this->resolveNewPath($path, $order->tenant_id, 'inspiration') : null;

                    if ($newPath === null) {
                        $newPaths[] = $path;
                        continue;
                    }

                    if ($this->copyFile($path, $newPath)) {
                        $newPaths[] = $newPath;
                        $changed = true;
                    } else {
                        $newPaths[] = $path;
                    }
                }

                if ($changed && !$this->dryRun) {
                    $order->inspiration_files = $newPaths;
                    $order->save();
                }
            }
        });
    }

    private function migrateTenantLogos(): void
    {
        $this->info('--- Tenant logo_path ---');

        Tenant::whereNotNull('logo_path')->chunkById(100, function ($tenants) {
            foreach ($tenants as $tenant) {
                $newPath = $this->resolveNewPath($tenant->logo_path, $tenant->id, 'logos');
                if ($newPath === null) {
                    continue;
                }

                if ($this->copyFile($tenant->logo_path, $newPath) && !$this->dryRun) {
                    $tenant->logo_path = $newPath;
                    $tenant->save();
                }
            }
        });
    }

    private function migrateTenantJsonMediaPaths(): void
    {
        $this->info('--- Tenant JSON media paths (site_content, gallery_images, ai_generated_content) ---');

        $jsonColumns = ['site_content', 'gallery_images', 'ai_generated_content'];

        Tenant::chunkById(100, function ($tenants) use ($jsonColumns) {
            foreach ($tenants as $tenant) {
                foreach ($jsonColumns as $column) {
                    $data = $tenant->{$column};
                    if (empty($data)) {
                        continue;
                    }

                    $changed = false;
                    $data = $this->rewriteMediaPaths($data, $tenant->id, $changed);

                    if ($changed && !$this->dryRun) {
                        $tenant->{$column} = $data;
                        $tenant->save();
                    }
                }
            }
        });
    }

    private function rewriteMediaPaths($node, int $tenantId, bool &$changed)
    {
        if (is_array($node)) {
            foreach ($node as $key => $value) {
                $node[$key] = $this->rewriteMediaPaths($value, $tenantId, $changed);
            }

            return $node;
        }

        if (!is_string($node)) {
            return $node;
        }

        $newPath = $this->resolveNewPath($node, $tenantId, 'media');
        if ($newPath === null) {
            return $node;
        }

        if ($this->copyFile($node, $newPath)) {
            $changed = true;

            return $newPath;
        }

        return $node;
    }

    private function migrateGalleryItems(): void
    {
        $this->info('--- GalleryItem image_url ---');

        GalleryItem::whereNotNull('image_url')->chunkById(200, function ($items) {
            foreach ($items as $item) {
                $newPath = $this->resolveNewPath($item->image_url, $item->tenant_id, 'gallery');
                if ($newPath === null) {
                    continue;
                }

                if ($this->copyFile($item->image_url, $newPath) && !$this->dryRun) {
                    $item->image_url = $newPath;
                    $item->save();
                }
            }
        });
    }
}
