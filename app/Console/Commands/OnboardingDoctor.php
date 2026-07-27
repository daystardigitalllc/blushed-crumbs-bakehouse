<?php

namespace App\Console\Commands;

use App\Models\GalleryItem;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class OnboardingDoctor extends Command
{
    protected $signature = 'onboarding:doctor';

    protected $description = 'Read-only diagnostic report for the AI onboarding rebuild: PHP extensions/limits, disk space, per-tenant upload sizes, gallery path prefixes, queue depths, and reachable Gemini models';

    public function handle(): int
    {
        $this->reportExtensions();
        $this->reportIniLimits();
        $this->reportDiskSpace();
        $this->reportTenantUploadSizes();
        $this->reportGalleryPathPrefixes();
        $this->reportQueueDepths();
        $this->reportGeminiModels();

        return self::SUCCESS;
    }

    private function reportExtensions(): void
    {
        $this->info('--- PHP extensions ---');

        $extensions = ['gd', 'imagick', 'exif', 'fileinfo'];

        $rows = collect($extensions)->map(fn ($ext) => [
            $ext,
            extension_loaded($ext) ? 'loaded' : 'MISSING',
        ]);

        $this->table(['Extension', 'Status'], $rows);
    }

    private function reportIniLimits(): void
    {
        $this->info('--- PHP ini limits ---');

        $keys = [
            'upload_max_filesize',
            'post_max_size',
            'max_execution_time',
            'max_input_time',
            'memory_limit',
            'max_file_uploads',
        ];

        $rows = collect($keys)->map(fn ($key) => [$key, ini_get($key) ?: 'not set']);

        $this->table(['Setting', 'Value'], $rows);
    }

    private function reportDiskSpace(): void
    {
        $this->info('--- Disk space ---');

        $paths = [
            'base_path' => base_path(),
            'storage_path' => storage_path(),
            'public_path' => public_path(),
        ];

        $rows = collect($paths)->map(function ($path, $label) {
            $free = @disk_free_space($path);
            $total = @disk_total_space($path);

            return [
                $label,
                $path,
                $free !== false ? $this->formatBytes($free) : 'unknown',
                $total !== false ? $this->formatBytes($total) : 'unknown',
            ];
        });

        $this->table(['Path', 'Location', 'Free', 'Total'], $rows);
    }

    private function reportTenantUploadSizes(): void
    {
        $this->info('--- Per-tenant upload sizes on disk ---');

        $locations = [
            'uploads/tenants/*' => public_path('uploads/tenants'),
            'storage/tenants/*' => storage_path('app/public/tenants'),
        ];

        $rows = collect();

        foreach ($locations as $label => $root) {
            if (!is_dir($root)) {
                continue;
            }

            foreach (glob($root . '/*', GLOB_ONLYDIR) ?: [] as $tenantDir) {
                $bytes = $this->directorySize($tenantDir);
                $rows->push([$label, basename($tenantDir), $bytes]);
            }
        }

        if ($rows->isEmpty()) {
            $this->line('No per-tenant upload directories found under public/uploads/tenants or storage/app/public/tenants.');
        } else {
            $sorted = $rows->sortByDesc(fn ($row) => $row[2])->values();
            $this->table(
                ['Location', 'Tenant ID', 'Size on disk'],
                $sorted->map(fn ($row) => [$row[0], $row[1], $this->formatBytes($row[2])])
            );
        }

        $flatUploads = public_path('uploads');
        if (is_dir($flatUploads)) {
            $flatFiles = collect(glob($flatUploads . '/*') ?: [])->filter(fn ($file) => is_file($file));
            $flatSize = $flatFiles->sum(fn ($file) => @filesize($file) ?: 0);
            $this->line("Legacy flat uploads (public/uploads/*, not tenant-scoped): {$flatFiles->count()} files, " . $this->formatBytes($flatSize));
        }
    }

    private function reportGalleryPathPrefixes(): void
    {
        $this->info('--- Gallery rows by path prefix ---');

        $buckets = [
            'storage/tenants/{id}/...' => '#^storage/tenants/\d+/#',
            'uploads/tenants/{id}/...' => '#^uploads/tenants/\d+/#',
            'uploads/... (legacy, not tenant-scoped)' => '#^uploads/(?!tenants/)#',
            'absolute URL (http/https)' => '#^https?://#',
        ];

        $counts = array_fill_keys(array_keys($buckets), 0);
        $unrecognized = 0;
        $total = 0;

        GalleryItem::query()->select('id', 'image_url')->chunkById(500, function ($items) use (&$counts, &$unrecognized, &$total, $buckets) {
            foreach ($items as $item) {
                $total++;
                $matched = false;

                foreach ($buckets as $label => $pattern) {
                    if ($item->image_url && preg_match($pattern, $item->image_url)) {
                        $counts[$label]++;
                        $matched = true;
                        break;
                    }
                }

                if (!$matched) {
                    $unrecognized++;
                }
            }
        });

        $rows = collect($counts)->map(fn ($count, $label) => [$label, $count])->values();
        $rows->push(['unrecognized / null', $unrecognized]);
        $rows->push(['TOTAL', $total]);

        $this->table(['Path prefix', 'Rows'], $rows);
    }

    private function reportQueueDepths(): void
    {
        $this->info('--- Queue depths ---');

        if (!$this->tableExists('jobs')) {
            $this->line('jobs table does not exist (migrations not run, or a non-database queue driver is in use).');

            return;
        }

        $pending = DB::table('jobs')
            ->select('queue', DB::raw('count(*) as count'))
            ->groupBy('queue')
            ->get();

        if ($pending->isEmpty()) {
            $this->line('No pending jobs.');
        } else {
            $this->table(['Queue', 'Pending'], $pending->map(fn ($row) => [$row->queue, $row->count]));
        }

        if ($this->tableExists('failed_jobs')) {
            $failedTotal = DB::table('failed_jobs')->count();
            $failedRecent = DB::table('failed_jobs')->where('failed_at', '>=', now()->subDay())->count();
            $this->line("Failed jobs: {$failedTotal} total, {$failedRecent} in the last 24h.");
        }
    }

    private function reportGeminiModels(): void
    {
        $this->info('--- Gemini model reachability ---');

        $apiKey = config('services.gemini.key');

        if (empty($apiKey)) {
            $this->warn('services.gemini.key is not set — skipping reachability checks.');

            return;
        }

        $models = array_unique(array_filter([
            config('services.gemini.model'),
            config('services.gemini.image_model'),
            'gemini-3.5-flash',
        ]));

        $rows = collect($models)->map(function ($model) use ($apiKey) {
            try {
                $response = Http::timeout(10)
                    ->get("https://generativelanguage.googleapis.com/v1beta/models/{$model}", ['key' => $apiKey]);

                return [$model, $response->successful() ? 'reachable' : "HTTP {$response->status()}"];
            } catch (\Throwable $e) {
                return [$model, 'error: ' . $e->getMessage()];
            }
        });

        $this->table(['Model', 'Status'], $rows);
    }

    private function directorySize(string $dir): int
    {
        $size = 0;

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }

        return $size;
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = (int) floor(log($bytes, 1024));
        $power = min($power, count($units) - 1);

        return round($bytes / (1024 ** $power), 2) . ' ' . $units[$power];
    }

    private function tableExists(string $table): bool
    {
        return DB::getSchemaBuilder()->hasTable($table);
    }
}
