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

    public static function createCompleteCheckoutSessionData(array $overrides = []): array
    {
        $defaults = [
            'id' => 'cs_test_123',
            'object' => 'checkout.session',
            'amount_subtotal' => 25000,
            'amount_total' => 25000,
            'branding' => null,
            'billing_address_collection' => 'auto',
            'cancel_url' => 'https://example.com/cancel',
            'created_at' => '2022-01-01T00:00:00Z',
            'expires_at' => '2022-01-01T01:00:00Z',
            'currency' => 'php',
            'customer_name_collection' => false,
            'last_updated' => '2022-01-01T00:00:00Z',
            'line_items' => [
                [
                    'name' => 'Pro Plan Monthly',
                    'amount' => 25000,
                    'description' => 'Pro Plan Monthly',
                    'quantity' => 1,
                    'image' => null,
                ],
            ],
            'livemode' => false,
            'locale' => 'en',
            'merchant' => [
                'name' => 'Test Merchant',
                'support_email' => 'support@example.com',
                'support_phone' => '+639151234567',
            ],
            'metadata' => [],
            'mode' => 'payment',
            'payment_method_types' => ['card', 'gcash'],
            'payment_status' => 'unpaid',
            'payment_url' => 'https://new.pay.magpie.im/cs_test_123',
            'phone_number_collection' => false,
            'require_auth' => false,
            'submit_type' => 'pay',
            'success_url' => 'https://example.com/success',
            'bank_code' => null,
            'billing' => null,
            'client_reference_id' => null,
            'customer' => null,
            'customer_email' => null,
            'customer_name' => null,
            'customer_phone' => null,
            'description' => null,
            'payment_details' => null,
            'shipping' => null,
            'shipping_address_collection' => null,
        ];

        return array_merge($defaults, $overrides);
    }

    public function testCreate(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new CheckoutSessionsResource($client);

        $params = [
            'line_items' => [
                [
                    'name' => 'Pro Plan Monthly',
                    'amount' => 25000,
                    'description' => 'Pro Plan Monthly',
                    'quantity' => 1,
                ],
            ],
            'success_url' => 'https://example.com/success',
            'cancel_url' => 'https://example.com/cancel',
            'customer_email' => 'customer@example.com',
        ];

        $expectedResponse = self::createCompleteCheckoutSessionData([
            'id' => 'cs_test_123',
            'customer_email' => 'customer@example.com',
            'payment_url' => 'https://new.pay.magpie.im/cs_test_123',
            'payment_status' => 'unpaid',
        ]);

        // BaseResource calls post() with basePath which is '' for checkout sessions (ltrim('/', '/'))
        $client->shouldReceive('post')
            ->once()
            ->with('', $params, ['base_uri' => 'https://api.pay.magpie.im/'])
            ->andReturn($expectedResponse);

        $result = $resource->create($params);

        // Test array access (backward compatibility)
        $this->assertSame('cs_test_123', $result['id']);
        $this->assertSame('checkout.session', $result['object']);
        $this->assertSame(25000, $result['amount_total']);
        $this->assertSame('unpaid', $result['payment_status']);
        $this->assertSame('customer@example.com', $result['customer_email']);
        
        // Test object access (new hybrid API)
        $this->assertSame('cs_test_123', $result->id);
        $this->assertSame('checkout.session', $result->object);
        $this->assertSame(25000, $result->amount_total);
        $this->assertSame('unpaid', $result->payment_status->value);
        $this->assertSame('customer@example.com', $result->customer_email);
    }

    public function testRetrieve(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new CheckoutSessionsResource($client);

        $sessionId = 'cs_test_123';
        $expectedResponse = self::createCompleteCheckoutSessionData([
            'id' => $sessionId,
            'payment_status' => 'paid',
        ]);

        $client->shouldReceive('get')
            ->once()
            ->with("/{$sessionId}", null, ['base_uri' => 'https://api.pay.magpie.im/'])
            ->andReturn($expectedResponse);

        $result = $resource->retrieve($sessionId);

        // Test array access (backward compatibility)
        $this->assertSame($sessionId, $result['id']);
        $this->assertSame('checkout.session', $result['object']);
        $this->assertSame('paid', $result['payment_status']);
        $this->assertSame(25000, $result['amount_total']);
        
        // Test object access (new hybrid API)
        $this->assertSame($sessionId, $result->id);
        $this->assertSame('checkout.session', $result->object);
        $this->assertSame('paid', $result->payment_status->value);
        $this->assertSame(25000, $result->amount_total);
    }

    public function testCapture(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new CheckoutSessionsResource($client);

        $sessionId = 'cs_test_123';
        $params = ['amount' => 20000];

        $expectedResponse = self::createCompleteCheckoutSessionData([
            'id' => $sessionId,
            'amount_total' => 25000,
            'payment_status' => 'paid',
        ]);
        $expectedResponse['amount_captured'] = 20000;

        // customResourceAction calls $client->request()
        $client->shouldReceive('request')
            ->once()
            ->with('POST', "/{$sessionId}/capture", $params, ['base_uri' => 'https://api.pay.magpie.im/'])
            ->andReturn($expectedResponse);

        $result = $resource->capture($sessionId, $params);

        // Test array access (backward compatibility)
        $this->assertSame($sessionId, $result['id']);
        $this->assertSame('checkout.session', $result['object']);
        $this->assertSame(20000, $result['amount_captured']);
        $this->assertSame('paid', $result['payment_status']);
        
        // Test object access (new hybrid API)
        $this->assertSame($sessionId, $result->id);
        $this->assertSame('checkout.session', $result->object);
        $this->assertSame('paid', $result->payment_status->value);
        $this->assertSame(25000, $result->amount_total);
    }

    public function testExpire(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new CheckoutSessionsResource($client);

        $sessionId = 'cs_test_123';

        $expectedResponse = self::createCompleteCheckoutSessionData([
            'id' => $sessionId,
            'payment_status' => 'expired',
        ]);

        $client->shouldReceive('request')
            ->once()
            ->with('POST', "/{$sessionId}/expire", null, ['base_uri' => 'https://api.pay.magpie.im/'])
            ->andReturn($expectedResponse);

        $result = $resource->expire($sessionId);

        // Test array access (backward compatibility)
        $this->assertSame($sessionId, $result['id']);
        $this->assertSame('checkout.session', $result['object']);
        $this->assertSame('expired', $result['payment_status']);
        
        // Test object access (new hybrid API)
        $this->assertSame($sessionId, $result->id);
        $this->assertSame('checkout.session', $result->object);
        $this->assertSame('expired', $result->payment_status->value);
    }

    public function testCreateWithOptions(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new CheckoutSessionsResource($client);

        $params = [
            'line_items' => [
                [
                    'name' => 'Premium Service',
                    'amount' => 30000,
                    'description' => 'Premium Service',
                    'quantity' => 1,
                ],
            ],
            'success_url' => 'https://example.com/success',
            'cancel_url' => 'https://example.com/cancel',
        ];

        $options = ['idempotency_key' => 'test_key_123', 'base_uri' => 'https://api.pay.magpie.im/'];

        $expectedResponse = self::createCompleteCheckoutSessionData([
            'id' => 'cs_test_456',
            'amount_subtotal' => 30000,
            'amount_total' => 30000,
            'line_items' => [
                [
                    'amount' => 30000,
                    'description' => 'Premium Service',
                    'quantity' => 1,
                    'image' => null,
                ],
            ],
        ]);

        $client->shouldReceive('post')
            ->once()
            ->with('', $params, $options)
            ->andReturn($expectedResponse);

        $result = $resource->create($params, $options);

        // Test array access (backward compatibility)
        $this->assertSame('cs_test_456', $result['id']);
        $this->assertSame('checkout.session', $result['object']);
        $this->assertSame(30000, $result['amount_total']);
        
        // Test object access (new hybrid API)
        $this->assertSame('cs_test_456', $result->id);
        $this->assertSame('checkout.session', $result->object);
        $this->assertSame(30000, $result->amount_total);
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
            ->with('', $params, ['base_uri' => 'https://api.pay.magpie.im/'])
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

        $this->assertSame('https://api.pay.magpie.im/', $baseUrlProperty->getValue($resource));
    }
}
