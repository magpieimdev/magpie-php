<?php

declare(strict_types=1);

namespace Magpie\Tests\Integration;

use Magpie\DTOs\Responses\Source;
use Magpie\Enums\SourceType;
use PHPUnit\Framework\TestCase;

/**
 * Integration test for hybrid API functionality.
 * 
 * This test demonstrates that DTOs can be accessed both as objects (new way)
 * and as arrays (backward compatible way), fulfilling the hybrid API design.
 */
class HybridApiTest extends TestCase
{
    public function testSourceHybridAccess(): void
    {
        // Sample data that would come from API
        $apiData = [
            'id' => 'src_test_123',
            'object' => 'source',
            'type' => 'card',
            'redirect' => [
                'success' => 'https://example.com/success',
                'fail' => 'https://example.com/fail'
            ],
            'vaulted' => true,
            'used' => false,
            'livemode' => false,
            'created_at' => '2024-01-01T00:00:00Z',
            'updated_at' => '2024-01-01T00:00:00Z',
            'metadata' => ['custom_field' => 'value'],
        ];

        // Create hybrid object from API data
        $source = Source::fromArray($apiData);

        // Test object access (new way)
        $this->assertSame('src_test_123', $source->id);
        $this->assertSame('source', $source->object);
        $this->assertSame(SourceType::CARD, $source->type);
        $this->assertTrue($source->vaulted);
        $this->assertFalse($source->used);
        $this->assertFalse($source->livemode);
        $this->assertSame(['custom_field' => 'value'], $source->metadata);

        // Test array access (backward compatible way)
        $this->assertSame('src_test_123', $source['id']);
        $this->assertSame('source', $source['object']);
        $this->assertSame('card', $source['type']);
        $this->assertTrue($source['vaulted']);
        $this->assertFalse($source['used']);
        $this->assertFalse($source['livemode']);
        $this->assertSame(['custom_field' => 'value'], $source['metadata']);

        // Test that object properties and array access return consistent data
        $this->assertSame($source->id, $source['id']);
        $this->assertSame($source->object, $source['object']);
        $this->assertSame($source->type->value, $source['type']);
        $this->assertSame($source->vaulted, $source['vaulted']);
        $this->assertSame($source->used, $source['used']);
        $this->assertSame($source->livemode, $source['livemode']);
        $this->assertSame($source->metadata, $source['metadata']);
    }

    public function testArrayAccessMethods(): void
    {
        $apiData = [
            'id' => 'src_test_456',
            'object' => 'source',
            'type' => 'gcash',
            'redirect' => [
                'success' => 'https://example.com/success',
                'fail' => 'https://example.com/fail'
            ],
            'vaulted' => false,
            'used' => true,
            'livemode' => true,
            'created_at' => '2024-01-01T00:00:00Z',
            'updated_at' => '2024-01-01T00:00:00Z',
            'metadata' => [],
        ];

        $source = Source::fromArray($apiData);

        // Test isset
        $this->assertTrue(isset($source['id']));
        $this->assertTrue(isset($source['type']));
        $this->assertFalse(isset($source['nonexistent']));

        // Test array access modification (for backward compatibility)
        $source['custom_field'] = 'test_value';
        $this->assertSame('test_value', $source['custom_field']);

        // Test unset
        unset($source['custom_field']);
        $this->assertFalse(isset($source['custom_field']));
        $this->assertNull($source['custom_field']);
    }
}