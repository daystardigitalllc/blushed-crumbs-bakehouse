<?php

namespace Tests\Feature;

use App\Services\AiContentService;
use Tests\TestCase;

/**
 * Regression test for the Phase 5 fix: mapToSiteContent() was silently
 * dropping seo_title/seo_description even though the AI prompt always asks
 * for them (see Tenant::siteContentSchema()'s former docblock note).
 */
class AiContentServiceSeoFixTest extends TestCase
{
    public function test_map_to_site_content_keeps_seo_fields()
    {
        $service = new AiContentService();

        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('mapToSiteContent');
        $method->setAccessible(true);

        $ai = [
            'hero_headline' => 'Test Bakery',
            'seo_title' => 'Test Bakery | Custom Cakes',
            'seo_description' => 'Order custom cakes online.',
        ];

        $result = $method->invoke($service, $ai, ['name' => 'Test Bakery']);

        $this->assertSame('Test Bakery | Custom Cakes', $result['seo_title']);
        $this->assertSame('Order custom cakes online.', $result['seo_description']);
    }

    public function test_map_to_site_content_defaults_seo_fields_to_empty_string_when_absent()
    {
        $service = new AiContentService();

        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('mapToSiteContent');
        $method->setAccessible(true);

        $result = $method->invoke($service, [], ['name' => 'Test Bakery']);

        $this->assertArrayHasKey('seo_title', $result);
        $this->assertArrayHasKey('seo_description', $result);
        $this->assertSame('', $result['seo_title']);
    }
}
