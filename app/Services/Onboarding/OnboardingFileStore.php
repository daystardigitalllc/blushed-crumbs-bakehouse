<?php

namespace App\Services\Onboarding;

use App\Models\Onboarding\OnboardingDraft;
use App\Models\Onboarding\OnboardingEvent;
use App\Models\Onboarding\OnboardingFile;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

/**
 * Turns one uploaded file into an OnboardingFile row: sniffs the real mime
 * (never trusts the client's Content-Type), hashes for dedupe, enforces the
 * per-draft quota, stores the original into private draft storage, and — for
 * images — runs ImageProcessor for derivatives and a local quality score.
 *
 * Every failure mode here degrades to a tracked row with a status, never an
 * exception that would surface as a 500: duplicate uploads, quota overruns,
 * and unprocessable formats (legacy .doc, HEIC without Imagick, a corrupt
 * file) all still produce a row so the baker sees what happened to their upload.
 */
class OnboardingFileStore
{
    public const OUTCOME_CREATED = 'created';
    public const OUTCOME_DUPLICATE = 'duplicate';
    public const OUTCOME_QUOTA_EXCEEDED = 'quota_exceeded';

    public function __construct(private ImageProcessor $imageProcessor)
    {
    }

    /**
     * @return array{outcome:string,file:?OnboardingFile}
     */
    public function store(OnboardingDraft $draft, UploadedFile $upload, ?string $originalFilename = null): array
    {
        $tenantId = $draft->tenant_id;
        $draftId = $draft->id;
        $tmpPath = $upload->getRealPath();
        $contentHash = hash_file('sha256', $tmpPath);
        $fileSize = $upload->getSize();

        if ($this->quotaExceeded($draftId, $fileSize)) {
            $this->logEvent($draft, 'file_rejected_quota', $originalFilename ?? $upload->getClientOriginalName());

            return ['outcome' => self::OUTCOME_QUOTA_EXCEEDED, 'file' => null];
        }

        $mime = $upload->getMimeType(); // sniffed via fileinfo, not the client-supplied header
        $kind = $this->classify($mime);
        $extension = $this->extensionForMime($mime, $upload->getClientOriginalExtension());
        $baseFilename = (string) Str::uuid();

        $originalsDir = TenantMediaPath::draftOriginalsDir($tenantId, $draftId);
        TenantMediaPath::ensureDir($originalsDir);
        $originalPath = "{$originalsDir}/{$baseFilename}.{$extension}";
        $upload->move($originalsDir, "{$baseFilename}.{$extension}");

        $attributes = [
            'draft_id' => $draftId,
            'tenant_id' => $tenantId,
            'original_filename' => $originalFilename ?? $upload->getClientOriginalName(),
            'kind' => $kind,
            'path' => $originalPath,
            'mime_type' => $mime,
            'file_size' => $fileSize,
            'content_hash' => $contentHash,
            'status' => 'pending',
        ];

        if ($kind === 'image') {
            try {
                $result = $this->imageProcessor->process(
                    $originalPath,
                    $mime,
                    TenantMediaPath::draftRoot($tenantId, $draftId),
                    $baseFilename
                );

                $attributes['width'] = $result['width'];
                $attributes['height'] = $result['height'];
                $attributes['quality_score'] = $result['quality_score'];
                $attributes['is_hero_candidate'] = $result['is_hero_candidate'];
                $attributes['status'] = 'pending'; // ready for the extraction pipeline (later phase)
            } catch (\Throwable $e) {
                $attributes['status'] = 'unsupported';
                $attributes['error_message'] = $e->getMessage();
            }
        } elseif ($kind === 'unsupported') {
            $attributes['status'] = 'unsupported';
        }

        try {
            $file = OnboardingFile::create($attributes);
        } catch (UniqueConstraintViolationException $e) {
            @unlink($originalPath);
            $this->logEvent($draft, 'file_duplicate', $attributes['original_filename']);

            return ['outcome' => self::OUTCOME_DUPLICATE, 'file' => null];
        }

        $this->logEvent($draft, 'file_uploaded', $attributes['original_filename'], ['file_id' => $file->id, 'status' => $file->status]);

        return ['outcome' => self::OUTCOME_CREATED, 'file' => $file];
    }

    private function quotaExceeded(int $draftId, int $incomingBytes): bool
    {
        $maxFiles = (int) config('onboarding.max_files_per_draft', 500);
        $maxBytes = (int) config('onboarding.max_bytes_per_draft', 2 * 1024 * 1024 * 1024);

        $existing = OnboardingFile::where('draft_id', $draftId)
            ->selectRaw('COUNT(*) as file_count, COALESCE(SUM(file_size), 0) as total_bytes')
            ->first();

        if (($existing->file_count ?? 0) + 1 > $maxFiles) {
            return true;
        }

        return (($existing->total_bytes ?? 0) + $incomingBytes) > $maxBytes;
    }

    private function classify(string $mime): string
    {
        if ($this->imageProcessor->isSupportedImageMime($mime)) {
            return 'image';
        }

        if (in_array($mime, config('onboarding.allowed_doc_mimes', []), true)) {
            return 'pdf';
        }

        return 'unsupported';
    }

    private function extensionForMime(string $mime, ?string $clientExtension): string
    {
        $known = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            'image/heic' => 'heic',
            'image/heif' => 'heif',
            'application/pdf' => 'pdf',
        ];

        if (isset($known[$mime])) {
            return $known[$mime];
        }

        $sanitized = strtolower(preg_replace('/[^a-z0-9]/i', '', $clientExtension ?? ''));

        return $sanitized !== '' ? substr($sanitized, 0, 10) : 'bin';
    }

    private function logEvent(OnboardingDraft $draft, string $type, ?string $filename, array $extra = []): void
    {
        OnboardingEvent::create([
            'draft_id' => $draft->id,
            'tenant_id' => $draft->tenant_id,
            'type' => $type,
            'message' => $filename,
            'payload' => $extra ?: null,
        ]);
    }
}
