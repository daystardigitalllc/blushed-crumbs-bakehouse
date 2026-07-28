<?php

namespace App\Services\Onboarding;

/**
 * Converts an uploaded image into three WebP/JPEG derivatives and produces a
 * free local quality score. Imagick is preferred (it alone can read HEIC —
 * required for iPhone photos — and has better resize quality); GD is the
 * fallback for hosts without the Imagick extension. Anything neither backend
 * can read (HEIC without Imagick) throws, and the caller marks the file
 * "unsupported" instead of failing the whole upload.
 */
class ImageProcessor
{
    private const HEIC_MIMES = ['image/heic', 'image/heif'];

    public function backend(): string
    {
        return extension_loaded('imagick') ? 'imagick' : (extension_loaded('gd') ? 'gd' : 'none');
    }

    public function isSupportedImageMime(string $mime): bool
    {
        return in_array($mime, config('onboarding.allowed_image_mimes', []), true);
    }

    /**
     * @param string $sourcePath absolute path to the uploaded original
     * @param string $mime sniffed mime type
     * @param string $derivativesRootDir directory containing thumb/display/ai subdirs
     * @param string $baseFilename filename (without extension) shared by all derivatives
     * @return array{width:int,height:int,quality_score:float,is_hero_candidate:bool,backend:string,derivatives:array<string,string>}
     */
    public function process(string $sourcePath, string $mime, string $derivativesRootDir, string $baseFilename): array
    {
        if (!$this->isSupportedImageMime($mime)) {
            throw new \RuntimeException("Unsupported image mime: {$mime}");
        }

        $isHeic = in_array($mime, self::HEIC_MIMES, true);

        if (extension_loaded('imagick')) {
            return $this->processWithImagick($sourcePath, $derivativesRootDir, $baseFilename);
        }

        if ($isHeic) {
            throw new \RuntimeException('HEIC/HEIF requires the Imagick extension, which is not available on this host.');
        }

        if (!extension_loaded('gd')) {
            throw new \RuntimeException('Neither Imagick nor GD is available on this host.');
        }

        return $this->processWithGd($sourcePath, $mime, $derivativesRootDir, $baseFilename);
    }

    // ─── Imagick backend ───

    private function processWithImagick(string $sourcePath, string $derivativesRootDir, string $baseFilename): array
    {
        $image = new \Imagick($sourcePath);
        $image = $image->coalesceImages() ?: $image; // first frame if animated
        $image->setIteratorIndex(0);
        $this->imagickAutoOrient($image);
        $image->stripImage();

        $width = $image->getImageWidth();
        $height = $image->getImageHeight();

        $sharpness = $this->imagickSharpness($image);
        $qualityScore = $this->combineScores($width, $height, $sharpness);
        $isHero = $this->isHeroCandidate($width, $height, $qualityScore);

        $derivatives = [];
        foreach (config('onboarding.derivatives', []) as $key => $spec) {
            $dir = "{$derivativesRootDir}/{$key}";
            TenantMediaPath::ensureDir($dir);
            $ext = $spec['format'] === 'webp' ? 'webp' : 'jpg';
            $destPath = "{$dir}/{$baseFilename}.{$ext}";

            $derivative = clone $image;
            $derivative->resizeImage($spec['width'], $spec['width'], \Imagick::FILTER_LANCZOS, 1, true);

            if ($spec['format'] === 'webp') {
                $derivative->setImageFormat('webp');
                $derivative->setImageCompressionQuality($spec['quality']);
            } else {
                $derivative->setImageBackgroundColor(new \ImagickPixel('white'));
                $derivative = $derivative->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
                $derivative->setImageFormat('jpeg');
                $derivative->setImageCompressionQuality($spec['quality']);
            }

            $derivative->writeImage($destPath);
            $derivative->destroy();

            $derivatives[$key] = $destPath;
        }

        $image->destroy();

        return [
            'width' => $width,
            'height' => $height,
            'quality_score' => $qualityScore,
            'is_hero_candidate' => $isHero,
            'backend' => 'imagick',
            'derivatives' => $derivatives,
        ];
    }

    /**
     * Imagick::autoOrientImage() doesn't exist on every Imagick build —
     * missing on production's here (older pecl-imagick/ImageMagick 6
     * combos predate it). Every real photo was throwing
     * "Call to undefined method Imagick::autoOrientImage()" and getting
     * marked unsupported until this was caught live. Falls back to reading
     * the EXIF orientation tag directly and rotating manually — the same
     * information autoOrientImage() itself reads under the hood.
     */
    private function imagickAutoOrient(\Imagick $image): void
    {
        if (method_exists($image, 'autoOrientImage')) {
            $image->autoOrientImage();

            return;
        }

        $orientation = $image->getImageOrientation();

        switch ($orientation) {
            case \Imagick::ORIENTATION_BOTTOMRIGHT:
                $image->rotateImage(new \ImagickPixel(), 180);
                break;
            case \Imagick::ORIENTATION_RIGHTTOP:
                $image->rotateImage(new \ImagickPixel(), 90);
                break;
            case \Imagick::ORIENTATION_LEFTBOTTOM:
                $image->rotateImage(new \ImagickPixel(), -90);
                break;
        }

        $image->setImageOrientation(\Imagick::ORIENTATION_TOPLEFT);
    }

    private function imagickSharpness(\Imagick $source): float
    {
        $probe = clone $source;
        $probe->resizeImage(300, 300, \Imagick::FILTER_LANCZOS, 1, true);
        $probe->setImageFormat('jpeg');
        $probe->setImageCompressionQuality(90);
        $bytes = strlen($probe->getImageBlob());
        $pixels = max(1, $probe->getImageWidth() * $probe->getImageHeight());
        $probe->destroy();

        return $this->bytesPerPixelToSharpness($bytes, $pixels);
    }

    // ─── GD backend ───

    private function processWithGd(string $sourcePath, string $mime, string $derivativesRootDir, string $baseFilename): array
    {
        $im = $this->gdLoad($sourcePath, $mime);
        $im = $this->gdAutoOrient($im, $sourcePath);

        $width = imagesx($im);
        $height = imagesy($im);

        $sharpness = $this->gdSharpness($im);
        $qualityScore = $this->combineScores($width, $height, $sharpness);
        $isHero = $this->isHeroCandidate($width, $height, $qualityScore);

        $derivatives = [];
        foreach (config('onboarding.derivatives', []) as $key => $spec) {
            $dir = "{$derivativesRootDir}/{$key}";
            TenantMediaPath::ensureDir($dir);
            $ext = $spec['format'] === 'webp' ? 'webp' : 'jpg';
            $destPath = "{$dir}/{$baseFilename}.{$ext}";

            $resized = $this->gdResizeToFit($im, $spec['width']);

            if ($spec['format'] === 'webp') {
                imagewebp($resized, $destPath, $spec['quality']);
            } else {
                $flattened = $this->gdFlattenToWhite($resized);
                imagejpeg($flattened, $destPath, $spec['quality']);
                imagedestroy($flattened);
            }

            imagedestroy($resized);
            $derivatives[$key] = $destPath;
        }

        imagedestroy($im);

        return [
            'width' => $width,
            'height' => $height,
            'quality_score' => $qualityScore,
            'is_hero_candidate' => $isHero,
            'backend' => 'gd',
            'derivatives' => $derivatives,
        ];
    }

    private function gdLoad(string $path, string $mime)
    {
        $im = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($path),
            'image/png' => @imagecreatefrompng($path),
            'image/webp' => @imagecreatefromwebp($path),
            'image/gif' => @imagecreatefromgif($path),
            default => false,
        };

        if ($im === false) {
            throw new \RuntimeException("GD failed to read image ({$mime}).");
        }

        return $im;
    }

    private function gdAutoOrient($im, string $path)
    {
        if (!function_exists('exif_read_data')) {
            return $im;
        }

        $exif = @exif_read_data($path);
        $orientation = $exif['Orientation'] ?? 1;

        $rotated = match ($orientation) {
            3 => imagerotate($im, 180, 0),
            6 => imagerotate($im, -90, 0),
            8 => imagerotate($im, 90, 0),
            default => null,
        };

        if ($rotated !== null) {
            imagedestroy($im);

            return $rotated;
        }

        return $im;
    }

    private function gdResizeToFit($im, int $maxDim)
    {
        $w = imagesx($im);
        $h = imagesy($im);
        $scale = min(1, $maxDim / max($w, $h));
        $newW = max(1, (int) round($w * $scale));
        $newH = max(1, (int) round($h * $scale));

        $dst = imagecreatetruecolor($newW, $newH);
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
        imagefilledrectangle($dst, 0, 0, $newW, $newH, $transparent);
        imagecopyresampled($dst, $im, 0, 0, 0, 0, $newW, $newH, $w, $h);

        return $dst;
    }

    private function gdFlattenToWhite($im)
    {
        $w = imagesx($im);
        $h = imagesy($im);
        $flat = imagecreatetruecolor($w, $h);
        $white = imagecolorallocate($flat, 255, 255, 255);
        imagefilledrectangle($flat, 0, 0, $w, $h, $white);
        imagecopy($flat, $im, 0, 0, 0, 0, $w, $h);

        return $flat;
    }

    private function gdSharpness($im): float
    {
        $probe = $this->gdResizeToFit($im, 300);
        ob_start();
        imagejpeg($probe, null, 90);
        $bytes = strlen(ob_get_clean());
        $pixels = max(1, imagesx($probe) * imagesy($probe));
        imagedestroy($probe);

        return $this->bytesPerPixelToSharpness($bytes, $pixels);
    }

    // ─── Shared scoring ───

    /**
     * A sharp/detailed image compresses larger than a blurry/flat one at the
     * same JPEG quality — this is a well-known, backend-agnostic proxy for
     * sharpness that avoids fragile Imagick channel-statistics APIs.
     */
    private function bytesPerPixelToSharpness(int $bytes, int $pixels): float
    {
        $bytesPerPixel = $bytes / $pixels;

        return round(min(1.0, $bytesPerPixel / 0.25) * 100, 2);
    }

    private function combineScores(int $width, int $height, float $sharpnessScore): float
    {
        $resolutionScore = min(100, ($width * $height) / (2000 * 1500) * 100);

        return round(($resolutionScore * 0.5) + ($sharpnessScore * 0.5), 2);
    }

    private function isHeroCandidate(int $width, int $height, float $qualityScore): bool
    {
        if ($height <= 0) {
            return false;
        }

        $aspect = $width / $height;

        return $width > $height && $aspect >= 1.1 && $aspect <= 2.5 && $qualityScore >= 55;
    }
}
