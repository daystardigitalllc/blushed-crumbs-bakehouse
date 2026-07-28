<?php

namespace Tests\Feature;

use App\Services\Onboarding\ImageProcessor;
use App\Services\Onboarding\TenantMediaPath;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Regression test for a real production bug: Imagick::autoOrientImage()
 * doesn't exist on every Imagick build (missing on production's), so every
 * real photo threw "Call to undefined method Imagick::autoOrientImage()"
 * during processing and got silently marked 'unsupported' — invisible to
 * every other test in this suite since neither local dev nor the CI/test
 * environment has the imagick extension installed at all.
 *
 * Skipped (not failed) when imagick isn't loaded, same as this whole app's
 * local/test environments — this exists so it actually runs and means
 * something the moment it's executed somewhere with imagick present
 * (production itself, or a future CI image that installs it).
 */
class ImageProcessorImagickTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (!extension_loaded('imagick')) {
            $this->markTestSkipped('imagick extension not installed in this environment — see class docblock.');
        }
    }

    public function test_processing_a_real_jpeg_does_not_throw_on_orientation()
    {
        config(['onboarding.allowed_image_mimes' => ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/heic', 'image/heif']]);
        config(['onboarding.derivatives' => [
            'thumb' => ['width' => 400, 'format' => 'webp', 'quality' => 80],
            'display' => ['width' => 1920, 'format' => 'webp', 'quality' => 82],
            'ai' => ['width' => 1568, 'format' => 'jpeg', 'quality' => 82],
        ]]);

        $sourcePath = sys_get_temp_dir() . '/imagick-orient-test-' . Str::random(6) . '.jpg';
        $im = new \Imagick();
        $im->newImage(800, 600, new \ImagickPixel('red'));
        $im->setImageFormat('jpeg');
        $im->writeImage($sourcePath);
        $im->destroy();

        $derivativesRoot = storage_path('app/test-imagick-' . Str::random(6));
        TenantMediaPath::ensureDir($derivativesRoot);

        $processor = new ImageProcessor();
        $result = $processor->process($sourcePath, 'image/jpeg', $derivativesRoot, 'test');

        $this->assertSame('imagick', $result['backend']);
        $this->assertSame(800, $result['width']);
        $this->assertSame(600, $result['height']);
        $this->assertFileExists($derivativesRoot . '/display/test.webp');

        @unlink($sourcePath);
    }
}
