<?php

namespace App\Console\Commands;

use App\Models\Brand;
use App\Models\GalleryItem;
use App\Models\Order;
use App\Models\Review;
use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MigrateUploadsToStorageDisk extends Command
{
    protected $signature = 'tenants:migrate-uploads-to-storage {--dry-run : Report what would change without touching any files or records}';

    protected $description = 'Copy tenant uploads from public/uploads/tenants into the storage/app/public disk and update DB records to the new "storage/tenants/..." path prefix, so uploads survive future zero-downtime deploys. Originals are left in place, not deleted.';

    private bool $dryRun = true;
    private int $copied = 0;
    private int $skippedMissing = 0;

    public function handle(): int
    {
        $this->dryRun = (bool) $this->option('dry-run');
        $this->info($this->dryRun ? 'DRY RUN — no files or records will be changed.' : 'LIVE RUN — files will be copied and records updated.');

        $this->migrateTenantLogos();
        $this->migrateTenantJsonMediaPaths();
        $this->migrateGalleryItems();
        $this->migrateOrderInspirationFiles();
        $this->migrateReviewPhotos();
        $this->migrateBrandLogos();

        $this->info("Done. Files copied: {$this->copied}. Skipped (source missing): {$this->skippedMissing}.");

        if (!$this->dryRun && $this->copied > 0) {
            $this->info('Original files under public/uploads/tenants were left in place, not deleted.');
        }

        return self::SUCCESS;
    }

    /**
     * Given "uploads/tenants/{id}/{rest}", return "storage/tenants/{id}/{rest}",
     * or null if the path doesn't match that pattern (already migrated, an
     * external URL, or some other shape this migration shouldn't touch).
     */
    private function resolveNewPath(string $path): ?string
    {
        if (!preg_match('#^uploads/tenants/(\d+)/(.+)$#', $path, $m)) {
            return null;
        }

        return "storage/tenants/{$m[1]}/{$m[2]}";
    }

    private function copyFile(string $oldRelative, string $newRelative): bool
    {
        $oldFull = public_path($oldRelative);
        $newFull = Storage::disk('public')->path(substr($newRelative, strlen('storage/')));

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

        $this->copied++;

        return true;
    }

    private function migrateTenantLogos(): void
    {
        $this->info('--- Tenant logo_path ---');

        Tenant::whereNotNull('logo_path')->chunkById(100, function ($tenants) {
            foreach ($tenants as $tenant) {
                $newPath = $this->resolveNewPath($tenant->logo_path);
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
                    $data = $this->rewriteMediaPaths($data, $changed);

                    if ($changed && !$this->dryRun) {
                        $tenant->{$column} = $data;
                        $tenant->save();
                    }
                }
            }
        });
    }

    private function rewriteMediaPaths($node, bool &$changed)
    {
        if (is_array($node)) {
            foreach ($node as $key => $value) {
                $node[$key] = $this->rewriteMediaPaths($value, $changed);
            }

            return $node;
        }

        if (!is_string($node)) {
            return $node;
        }

        $newPath = $this->resolveNewPath($node);
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
                $newPath = $this->resolveNewPath($item->image_url);
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

    private function migrateOrderInspirationFiles(): void
    {
        $this->info('--- Order inspiration_files ---');

        Order::whereNotNull('inspiration_files')->chunkById(200, function ($orders) {
            foreach ($orders as $order) {
                $paths = $order->inspiration_files;
                if (!is_array($paths) || empty($paths)) {
                    continue;
                }

                $changedAny = false;
                $newPaths = [];

                foreach ($paths as $path) {
                    $newPath = is_string($path) ? $this->resolveNewPath($path) : null;

                    if ($newPath === null) {
                        $newPaths[] = $path;
                        continue;
                    }

                    if ($this->copyFile($path, $newPath)) {
                        $newPaths[] = $newPath;
                        $changedAny = true;
                    } else {
                        $newPaths[] = $path;
                    }
                }

                if ($changedAny && !$this->dryRun) {
                    $order->inspiration_files = $newPaths;
                    $order->save();
                }
            }
        });
    }

    private function migrateReviewPhotos(): void
    {
        $this->info('--- Review photo_url (defensive; expected zero rows) ---');

        Review::whereNotNull('photo_url')->chunkById(200, function ($reviews) {
            foreach ($reviews as $review) {
                $newPath = $this->resolveNewPath($review->photo_url);
                if ($newPath === null) {
                    continue;
                }

                if ($this->copyFile($review->photo_url, $newPath) && !$this->dryRun) {
                    $review->photo_url = $newPath;
                    $review->save();
                }
            }
        });
    }

    private function migrateBrandLogos(): void
    {
        $this->info('--- Brand logo_url (defensive; expected zero rows) ---');

        Brand::whereNotNull('logo_url')->chunkById(50, function ($brands) {
            foreach ($brands as $brand) {
                $newPath = $this->resolveNewPath($brand->logo_url);
                if ($newPath === null) {
                    continue;
                }

                if ($this->copyFile($brand->logo_url, $newPath) && !$this->dryRun) {
                    $brand->logo_url = $newPath;
                    $brand->save();
                }
            }
        });
    }
}
