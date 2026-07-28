<?php

namespace App\Services\Onboarding;

use Illuminate\Support\Facades\Storage;

/**
 * The single place that resolves, writes, and deletes tenant media —
 * everything else calls this rather than public_path()/Storage::disk()
 * directly. That's what makes a later move to object storage (see the
 * plan's storage roadmap) a contained change instead of a rewrite.
 *
 * Handles two eras of the same problem:
 *   - legacy/live tenant media (public/uploads/tenants/... and the newer
 *     storage/app/public/tenants/... disk), used today by galleries, logos,
 *     order inspiration photos;
 *   - private onboarding draft uploads (storage/app/onboarding/{tenant}/{draft}/),
 *     which must NOT be publicly guessable until the baker approves the draft.
 */
class TenantMediaPath
{
    /**
     * Delete a legacy-era tenant media file given its stored `image_url`-style
     * relative path. Extracted from AdminController::destroyGallery, which
     * had this exact both-prefix check inlined — kept byte-for-byte so the
     * refactor is behavior-preserving.
     */
    public static function deleteLegacy(?string $storedPath): bool
    {
        if (!$storedPath) {
            return false;
        }

        if (str_starts_with($storedPath, 'storage/')) {
            $relativePath = str_replace('storage/', '', $storedPath);

            return Storage::disk('public')->delete($relativePath);
        }

        if (str_starts_with($storedPath, 'uploads/')) {
            $legacyFullPath = public_path($storedPath);
            if (file_exists($legacyFullPath)) {
                return @unlink($legacyFullPath);
            }

            return false;
        }

        return false;
    }

    /**
     * Directory legacy gallery uploads are written to today.
     */
    public static function galleryUploadDir(int $tenantId): string
    {
        return public_path("uploads/tenants/{$tenantId}/gallery");
    }

    /**
     * Public URL that reaches the served gallery/display copy for a tenant,
     * once an onboarding import has copied a file out of the private draft
     * folder. This is the ONLY onboarding path that ends up under public/uploads.
     */
    public static function galleryDisplayPath(int $tenantId, string $filename): string
    {
        return "uploads/tenants/{$tenantId}/gallery/{$filename}";
    }

    /**
     * Private root for a draft's uploads. Never under public/ — unreviewed
     * content must not be publicly guessable.
     */
    public static function draftRoot(int $tenantId, int $draftId): string
    {
        return storage_path("app/onboarding/{$tenantId}/{$draftId}");
    }

    public static function draftOriginalsDir(int $tenantId, int $draftId): string
    {
        return self::draftRoot($tenantId, $draftId) . '/originals';
    }

    public static function draftDerivativeDir(int $tenantId, int $draftId, string $derivative): string
    {
        return self::draftRoot($tenantId, $draftId) . "/{$derivative}";
    }

    public static function ensureDir(string $path): void
    {
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }

    /**
     * Delete an entire draft's private storage — used by pruning.
     */
    public static function deleteDraftRoot(int $tenantId, int $draftId): bool
    {
        $root = self::draftRoot($tenantId, $draftId);

        if (!is_dir($root)) {
            return false;
        }

        return self::deleteDirectoryRecursive($root);
    }

    private static function deleteDirectoryRecursive(string $dir): bool
    {
        $items = scandir($dir);
        if ($items === false) {
            return false;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $dir . '/' . $item;
            if (is_dir($path)) {
                self::deleteDirectoryRecursive($path);
            } else {
                @unlink($path);
            }
        }

        return @rmdir($dir);
    }
}
