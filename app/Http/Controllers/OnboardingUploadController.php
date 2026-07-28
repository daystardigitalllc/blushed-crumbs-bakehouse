<?php

namespace App\Http\Controllers;

use App\Models\Onboarding\OnboardingDraft;
use App\Models\Onboarding\OnboardingFile;
use App\Services\Onboarding\OnboardingFileStore;
use App\Services\Onboarding\TenantMediaPath;
use Illuminate\Http\Request;

class OnboardingUploadController extends Controller
{
    public function __construct(private OnboardingFileStore $fileStore)
    {
    }

    /**
     * Stateless, one-file-per-request, signed-URL upload endpoint — outside
     * the `web` middleware group entirely (see bootstrap/app.php), so there's
     * no session lock to serialize concurrent uploads behind and no CSRF
     * token to juggle from a bulk uploader. The `signed` middleware verifies
     * the {draft} route parameter hasn't been tampered with; whoever issued
     * this URL already confirmed the requester owns that draft.
     */
    public function store(Request $request, OnboardingDraft $draft)
    {
        $request->validate([
            'file' => 'required|file|max:' . config('onboarding.max_file_size_kb'),
        ]);

        $upload = $request->file('file');
        $result = $this->fileStore->store($draft, $upload, $upload->getClientOriginalName());

        return match ($result['outcome']) {
            OnboardingFileStore::OUTCOME_DUPLICATE => response()->json(['status' => 'duplicate'], 200),
            OnboardingFileStore::OUTCOME_QUOTA_EXCEEDED => response()->json(['status' => 'quota_exceeded'], 422),
            default => response()->json([
                'status' => 'created',
                'file' => [
                    'id' => $result['file']->id,
                    'status' => $result['file']->status,
                    'kind' => $result['file']->kind,
                    'quality_score' => $result['file']->quality_score,
                ],
            ], 201),
        };
    }

    /**
     * Authenticated preview-streaming route — the review grid and any manual
     * inspection go through here rather than a public URL, since draft files
     * live in private storage until the baker approves the draft.
     */
    public function preview(Request $request, OnboardingFile $file, string $derivative = 'thumb')
    {
        $tenant = $request->user()->tenant;

        if (!$tenant || $file->tenant_id !== $tenant->id) {
            abort(403);
        }

        $path = $this->resolveDerivativePath($file, $derivative);

        if ($path === null || !is_file($path)) {
            abort(404);
        }

        return response()->file($path);
    }

    private function resolveDerivativePath(OnboardingFile $file, string $derivative): ?string
    {
        if ($derivative === 'original') {
            return $file->path;
        }

        if (!in_array($derivative, ['thumb', 'display', 'ai'], true)) {
            return null;
        }

        $baseFilename = pathinfo($file->path, PATHINFO_FILENAME);
        $extension = $derivative === 'ai' ? 'jpg' : 'webp';

        return TenantMediaPath::draftDerivativeDir($file->tenant_id, $file->draft_id, $derivative)
            . "/{$baseFilename}.{$extension}";
    }
}
