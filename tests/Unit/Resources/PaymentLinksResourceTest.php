<?php

declare(strict_types=1);

namespace Magpie\Tests\Unit\Resources;

use Magpie\Exceptions\MagpieException;
use Magpie\Http\Client;
use Magpie\Resources\PaymentLinksResource;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magpie\Resources\PaymentLinksResource
 */
class PaymentLinksResourceTest extends TestCase
{
    protected function tearDown(): void
    {
        \Mockery::close();
    }

    public static function createCompletePaymentLinkData(array $overrides = []): array
    {
        $defaults = [
            'id' => 'pl_test_123',
            'object' => 'payment_link',
            'active' => true,
            'allow_adjustable_quantity' => true,
            'branding' => null,
            'created' => 1640995200, // 2022-01-01
            'currency' => 'php',
            'internal_name' => 'Website Design Service',
            'line_items' => [
                [
                    'name' => 'Website Design Service',
                    'amount' => 100000,
                    'description' => 'Website Design Service',
                    'quantity' => 10,
                    'remaining' => 10,
                    'image' => 'https://example.com/service.jpg',
                ],
            ],
            'livemode' => false,
            'metadata' => [],
            'payment_method_types' => ['card', 'gcash'],
            'require_auth' => false,
            'updated' => 1640995200,
            'url' => 'https://buy.magpie.im/pl_test_123',
            'description' => null,
            'expiry' => null,
            'maximum_payments' => null,
            'phone_number_collection' => false,
            'redirect_url' => null,
            'shipping_address_collection' => null,
        ];

        return array_merge($defaults, $overrides);
    }

    public function testCreate(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new PaymentLinksResource($client);

        $params = [
            'internal_name' => 'Website Design Service',
            'allow_adjustable_quantity' => true,
            'line_items' => [
                [
                    'name' => 'Website Design Service',
                    'amount' => 100000,
                    'description' => 'Website Design Service',
                    'quantity' => 1,
                    'remaining' => 1,
                    'image' => 'https://example.com/service.jpg',
                ],
            ],
        ];

        $expectedResponse = self::createCompletePaymentLinkData([
            'id' => 'pl_test_123',
            'object' => 'payment_link',
            'internal_name' => 'Website Design Service',
            'url' => 'https://buy.magpie.im/pl_test_123',
            'active' => true,
            'allow_adjustable_quantity' => true,
            'line_items' => [
                [
                    'name' => 'Website Design Service',
                    'amount' => 100000,
                    'description' => 'Website Design Service',
                    'quantity' => 1,
                    'remaining' => 1,
                    'image' => 'https://example.com/service.jpg',
                ],
            ],
        ]);

        $client->shouldReceive('post')
            ->once()
            ->with('links', $params, ['base_uri' => 'https://buy.magpie.im/api/v1/'])
            ->andReturn($expectedResponse);

        $result = $resource->create($params);

        // Test array access (backward compatibility)
        $this->assertSame('pl_test_123', $result['id']);
        $this->assertSame('payment_link', $result['object']);
        $this->assertSame('Website Design Service', $result['internal_name']);
        $this->assertTrue($result['active']);
        $this->assertTrue($result['allow_adjustable_quantity']);

        // Test object access (new hybrid API)
        $this->assertSame('pl_test_123', $result->id);
        $this->assertSame('payment_link', $result->object);
        $this->assertSame('Website Design Service', $result->internal_name);
        $this->assertTrue($result->active);
        $this->assertTrue($result->allow_adjustable_quantity);
    }

    public function testRetrieve(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new PaymentLinksResource($client);

        $linkId = 'pl_test_123';
        $expectedResponse = self::createCompletePaymentLinkData([
            'id' => $linkId,
            'object' => 'payment_link',
            'internal_name' => 'Consultation Fee',
            'url' => 'https://buy.magpie.im/pl_test_123',
            'active' => true,
        ]);

        $client->shouldReceive('get')
            ->once()
            ->with("links/{$linkId}", null, ['base_uri' => 'https://buy.magpie.im/api/v1/'])
            ->andReturn($expectedResponse);

        $result = $resource->retrieve($linkId);

        // Test array access (backward compatibility)
        $this->assertSame($linkId, $result['id']);
        $this->assertSame('Consultation Fee', $result['internal_name']);
        $this->assertTrue($result['active']);

        // Test object access (new hybrid API)
        $this->assertSame($linkId, $result->id);
        $this->assertSame('Consultation Fee', $result->internal_name);
        $this->assertTrue($result->active);
    }

    public function testUpdate(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new PaymentLinksResource($client);

        $linkId = 'pl_test_123';
        $params = [
            'active' => false,
            'metadata' => ['campaign' => 'holiday-sale'],
        ];

        $expectedResponse = self::createCompletePaymentLinkData([
            'id' => $linkId,
            'object' => 'payment_link',
            'active' => false,
            'metadata' => ['campaign' => 'holiday-sale'],
        ]);

        $client->shouldReceive('put')
            ->once()
            ->with("links/{$linkId}", $params, ['base_uri' => 'https://buy.magpie.im/api/v1/'])
            ->andReturn($expectedResponse);

        $result = $resource->update($linkId, $params);

        // Test array access (backward compatibility)
        $this->assertSame($linkId, $result['id']);
        $this->assertFalse($result['active']);
        $this->assertSame('holiday-sale', $result['metadata']['campaign']);

        // Test object access (new hybrid API)
        $this->assertSame($linkId, $result->id);
        $this->assertFalse($result->active);
        $this->assertSame('holiday-sale', $result->metadata['campaign']);
    }

    public function testActivate(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new PaymentLinksResource($client);

        $linkId = 'pl_test_123';

        $expectedResponse = self::createCompletePaymentLinkData([
            'id' => $linkId,
            'object' => 'payment_link',
            'active' => true,
        ]);

        $client->shouldReceive('request')
            ->once()
            ->with('POST', "links/{$linkId}/activate", null, ['base_uri' => 'https://buy.magpie.im/api/v1/'])
            ->andReturn($expectedResponse);

        $result = $resource->activate($linkId);

        // Test array access (backward compatibility)
        $this->assertSame($linkId, $result['id']);
        $this->assertTrue($result['active']);

        // Test object access (new hybrid API)
        $this->assertSame($linkId, $result->id);
        $this->assertTrue($result->active);
    }

    public function testDeactivate(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new PaymentLinksResource($client);

        $linkId = 'pl_test_123';

        $expectedResponse = self::createCompletePaymentLinkData([
            'id' => $linkId,
            'object' => 'payment_link',
            'active' => false,
        ]);

        $client->shouldReceive('request')
            ->once()
            ->with('POST', "links/{$linkId}/deactivate", null, ['base_uri' => 'https://buy.magpie.im/api/v1/'])
            ->andReturn($expectedResponse);

        $result = $resource->deactivate($linkId);

        // Test array access (backward compatibility)
        $this->assertSame($linkId, $result['id']);
        $this->assertFalse($result['active']);

        // Test object access (new hybrid API)
        $this->assertSame($linkId, $result->id);
        $this->assertFalse($result->active);
    }

    public function testCreateWithMinimalParams(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new PaymentLinksResource($client);

        $params = [
            'line_items' => [
                [
                    'name' => 'Product Sale',
                    'amount' => 50000,
                    'description' => 'Product Sale',
                    'quantity' => 1,
                    'remaining' => 1,
                ],
            ],
        ];

        $expectedResponse = self::createCompletePaymentLinkData([
            'id' => 'pl_test_456',
            'object' => 'payment_link',
            'active' => true,
            'url' => 'https://buy.magpie.im/pl_test_456',
        ]);

        $client->shouldReceive('post')
            ->once()
            ->with('links', $params, ['base_uri' => 'https://buy.magpie.im/api/v1/'])
            ->andReturn($expectedResponse);

        $result = $resource->create($params);

        // Test array access (backward compatibility)
        $this->assertSame('pl_test_456', $result['id']);
        $this->assertTrue($result['active']);

        // Test object access (new hybrid API)
        $this->assertSame('pl_test_456', $result->id);
        $this->assertTrue($result->active);
    }

    public function testCreateWithOptions(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new PaymentLinksResource($client);

        $params = [
            'internal_name' => 'Premium Service',
            'line_items' => [
                [
                    'name' => 'Premium Service',
                    'amount' => 200000,
                    'description' => 'Premium Package',
                    'quantity' => 1,
                    'remaining' => 1,
                ],
            ],
        ];

        $options = ['idempotency_key' => 'link_create_123', 'base_uri' => 'https://buy.magpie.im/api/v1/'];

        $expectedResponse = self::createCompletePaymentLinkData([
            'id' => 'pl_test_789',
            'object' => 'payment_link',
        ]);

        $client->shouldReceive('post')
            ->once()
            ->with('links', $params, $options)
            ->andReturn($expectedResponse);

        $result = $resource->create($params, $options);

        // Test array access (backward compatibility)
        $this->assertSame('pl_test_789', $result['id']);

        // Test object access (new hybrid API)
        $this->assertSame('pl_test_789', $result->id);
    }

    public function testActivateWithOptions(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new PaymentLinksResource($client);

        $linkId = 'pl_test_123';
        $options = ['base_uri' => 'https://buy.magpie.im/api/v1/'];

        $expectedResponse = self::createCompletePaymentLinkData([
            'id' => $linkId,
            'object' => 'payment_link',
            'active' => true,
            'line_items' => [
                [
                    'name' => 'Service',
                    'amount' => 100000,
                    'description' => null,
                    'quantity' => 1,
                    'remaining' => 1,
                    'image' => null,
                ],
            ],
        ]);

        $client->shouldReceive('request')
            ->once()
            ->with('POST', "links/{$linkId}/activate", null, $options)
            ->andReturn($expectedResponse);

        $result = $resource->activate($linkId, $options);

        // Test array access (backward compatibility)
        $this->assertSame($linkId, $result['id']);
        $this->assertTrue($result['active']);
        $this->assertArrayHasKey('line_items', $result);

        // Test object access (new hybrid API)
        $this->assertSame($linkId, $result->id);
        $this->assertTrue($result->active);
        $this->assertArrayHasKey('line_items', $result);
    }

    public function testHandlesApiErrors(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new PaymentLinksResource($client);

        $params = [
            'line_items' => [],  // Invalid - empty line items
        ];

        $client->shouldReceive('post')
            ->once()
            ->with('links', $params, ['base_uri' => 'https://buy.magpie.im/api/v1/'])
            ->andThrow(new MagpieException('Line items are required', 'invalid_request_error'));

        $this->expectException(MagpieException::class);
        $this->expectExceptionMessage('Line items are required');

        $resource->create($params);
    }

    public function testUsesCorrectBaseUrl(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new PaymentLinksResource($client);

        // Verify that the resource is initialized with the correct base URL
        $reflectionClass = new \ReflectionClass($resource);
        $baseUrlProperty = $reflectionClass->getProperty('customBaseUrl');
        $baseUrlProperty->setAccessible(true);

        $this->assertSame('https://buy.magpie.im/api/v1/', $baseUrlProperty->getValue($resource));
    }

    public function testUpdateWithPartialParams(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new PaymentLinksResource($client);

        $linkId = 'pl_test_123';
        $params = [
            'internal_name' => 'Updated Service Name',
        ];

        $expectedResponse = self::createCompletePaymentLinkData([
            'id' => $linkId,
            'object' => 'payment_link',
            'internal_name' => 'Updated Service Name',
            'active' => true,  // Unchanged from previous state
        ]);

        $client->shouldReceive('put')
            ->once()
            ->with("links/{$linkId}", $params, ['base_uri' => 'https://buy.magpie.im/api/v1/'])
            ->andReturn($expectedResponse);

        $result = $resource->update($linkId, $params);

        // Test array access (backward compatibility)
        $this->assertSame($linkId, $result['id']);
        $this->assertSame('Updated Service Name', $result['internal_name']);
        $this->assertTrue($result['active']);

        // Test object access (new hybrid API)
        $this->assertSame($linkId, $result->id);
        $this->assertSame('Updated Service Name', $result->internal_name);
        $this->assertTrue($result->active);
    }
}
