<?php

declare(strict_types=1);

namespace Magpie\Tests\Unit\Resources;

use Magpie\Exceptions\MagpieException;
use Magpie\Http\Client;
use Magpie\Resources\CheckoutSessionsResource;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magpie\Resources\CheckoutSessionsResource
 */
class CheckoutSessionsResourceTest extends TestCase
{
    protected function tearDown(): void
    {
        \Mockery::close();
    }

    public function testCreate(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new CheckoutSessionsResource($client);

        $params = [
            'line_items' => [
                [
                    'amount' => 25000,
                    'description' => 'Pro Plan Monthly',
                    'quantity' => 1,
                ],
            ],
            'success_url' => 'https://example.com/success',
            'cancel_url' => 'https://example.com/cancel',
            'customer_email' => 'customer@example.com',
        ];

        $expectedResponse = [
            'id' => 'cs_test_123',
            'object' => 'checkout.session',
            'amount_total' => 25000,
            'currency' => 'php',
            'url' => 'https://new.pay.magpie.im/cs_test_123',
            'payment_status' => 'unpaid',
            'status' => 'open',
        ];

        // BaseResource calls post() with basePath which is '' for checkout sessions (ltrim('/', '/'))
        $client->shouldReceive('post')
            ->once()
            ->with('', $params, [])
            ->andReturn($expectedResponse);

        $result = $resource->create($params);

        $this->assertSame('cs_test_123', $result['id']);
        $this->assertSame('checkout.session', $result['object']);
        $this->assertSame(25000, $result['amount_total']);
        $this->assertSame('unpaid', $result['payment_status']);
    }

    public function testRetrieve(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new CheckoutSessionsResource($client);

        $sessionId = 'cs_test_123';
        $expectedResponse = [
            'id' => $sessionId,
            'object' => 'checkout.session',
            'amount_total' => 25000,
            'currency' => 'php',
            'payment_status' => 'paid',
            'status' => 'complete',
        ];

        $client->shouldReceive('get')
            ->once()
            ->with("/{$sessionId}", null, [])
            ->andReturn($expectedResponse);

        $result = $resource->retrieve($sessionId);

        $this->assertSame($sessionId, $result['id']);
        $this->assertSame('paid', $result['payment_status']);
        $this->assertSame('complete', $result['status']);
    }

    public function testCapture(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new CheckoutSessionsResource($client);

        $sessionId = 'cs_test_123';
        $params = ['amount' => 20000];

        $expectedResponse = [
            'id' => $sessionId,
            'object' => 'checkout.session',
            'amount_total' => 25000,
            'amount_captured' => 20000,
            'payment_status' => 'paid',
            'status' => 'complete',
        ];

        // customResourceAction calls $client->request()
        $client->shouldReceive('request')
            ->once()
            ->with('POST', "/{$sessionId}/capture", $params, [])
            ->andReturn($expectedResponse);

        $result = $resource->capture($sessionId, $params);

        $this->assertSame($sessionId, $result['id']);
        $this->assertSame(20000, $result['amount_captured']);
        $this->assertSame('paid', $result['payment_status']);
    }

    public function testExpire(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new CheckoutSessionsResource($client);

        $sessionId = 'cs_test_123';

        $expectedResponse = [
            'id' => $sessionId,
            'object' => 'checkout.session',
            'status' => 'expired',
        ];

        $client->shouldReceive('request')
            ->once()
            ->with('POST', "/{$sessionId}/expire", null, [])
            ->andReturn($expectedResponse);

        $result = $resource->expire($sessionId);

        $this->assertSame($sessionId, $result['id']);
        $this->assertSame('expired', $result['status']);
    }

    public function testCreateWithOptions(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new CheckoutSessionsResource($client);

        $params = [
            'line_items' => [
                [
                    'amount' => 30000,
                    'description' => 'Premium Service',
                    'quantity' => 1,
                ],
            ],
            'success_url' => 'https://example.com/success',
            'cancel_url' => 'https://example.com/cancel',
        ];

        $options = ['idempotency_key' => 'test_key_123'];

        $expectedResponse = [
            'id' => 'cs_test_456',
            'object' => 'checkout.session',
        ];

        $client->shouldReceive('post')
            ->once()
            ->with('', $params, $options)
            ->andReturn($expectedResponse);

        $result = $resource->create($params, $options);

        $this->assertSame('cs_test_456', $result['id']);
    }

    public function testHandlesApiErrors(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new CheckoutSessionsResource($client);

        $params = [
            'line_items' => [],  // Invalid - empty line items
            'success_url' => 'https://example.com/success',
            'cancel_url' => 'https://example.com/cancel',
        ];

        $client->shouldReceive('post')
            ->once()
            ->with('', $params, [])
            ->andThrow(new MagpieException('Line items cannot be empty', 'invalid_request_error'));

        $this->expectException(MagpieException::class);
        $this->expectExceptionMessage('Line items cannot be empty');

        $resource->create($params);
    }

    public function testUsesCorrectBaseUrl(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new CheckoutSessionsResource($client);

        // Verify that the resource is initialized with the correct base URL for checkout sessions
        $reflectionClass = new \ReflectionClass($resource);
        $baseUrlProperty = $reflectionClass->getProperty('customBaseUrl');
        $baseUrlProperty->setAccessible(true);

        $this->assertSame('https://new.pay.magpie.im', $baseUrlProperty->getValue($resource));
    }
}
