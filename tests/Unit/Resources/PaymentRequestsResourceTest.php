<?php

declare(strict_types=1);

namespace Magpie\Tests\Unit\Resources;

use Magpie\Exceptions\MagpieException;
use Magpie\Http\Client;
use Magpie\Resources\PaymentRequestsResource;
use Magpie\Tests\Unit\Resources\ChargesResourceTest;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magpie\Resources\PaymentRequestsResource
 */
class PaymentRequestsResourceTest extends TestCase
{
    protected function tearDown(): void
    {
        \Mockery::close();
    }

    public static function createCompletePaymentRequestData(array $overrides = []): array
    {
        $defaults = [
            'id' => 'pr_test_123',
            'object' => 'payment_request',
            'account_name' => 'Test Merchant',
            'branding' => null,
            'created' => 1640995200, // 2022-01-01
            'currency' => 'php',
            'customer' => 'cus_test_123',
            'customer_email' => 'customer@example.com',
            'customer_name' => 'Jane Smith',
            'delivery_methods' => ['email', 'sms'],
            'delivered' => [
                'email' => true,
                'sms' => true,
            ],
            'line_items' => [
                [
                    'name' => 'Monthly Subscription Payment',
                    'amount' => 50000,
                    'description' => 'Monthly Subscription Payment',
                    'quantity' => 1,
                    'image' => null,
                ],
            ],
            'livemode' => false,
            'metadata' => [],
            'number' => 'PR-2022-001',
            'paid' => false,
            'payment_method_types' => ['card', 'gcash'],
            'payment_request_url' => 'https://request.magpie.im/pr_test_123',
            'require_auth' => false,
            'subtotal' => 50000,
            'total' => 50000,
            'updated' => 1640995200,
            'voided' => false,
            'account_support_email' => null,
            'customer_phone' => null,
            'message' => null,
            'paid_at' => null,
            'payment_details' => null,
            'voided_at' => null,
            'void_reason' => null,
        ];

        return array_merge($defaults, $overrides);
    }

    public function testCreate(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new PaymentRequestsResource($client);

        $params = [
            'amount' => 50000,
            'currency' => 'php',
            'description' => 'Monthly Subscription Payment',
            'recipient' => [
                'name' => 'Jane Smith',
                'email' => 'jane@example.com',
                'phone' => '+639151234567',
            ],
            'send_email' => true,
            'send_sms' => true,
        ];

        $expectedResponse = self::createCompletePaymentRequestData([
            'id' => 'pr_test_123',
            'customer_name' => 'Jane Smith',
            'customer_email' => 'jane@example.com',
            'customer_phone' => '+639151234567',
            'paid' => false,
            'voided' => false,
        ]);

        $client->shouldReceive('post')
            ->once()
            ->with('requests', $params, ['base_uri' => 'https://request.magpie.im/api/v1/'])
            ->andReturn($expectedResponse);

        $result = $resource->create($params);

        // Test array access (backward compatibility)
        $this->assertSame('pr_test_123', $result['id']);
        $this->assertSame('payment_request', $result['object']);
        $this->assertSame(50000, $result['total']);
        $this->assertFalse($result['paid']);
        $this->assertSame('Jane Smith', $result['customer_name']);
        
        // Test object access (new hybrid API)
        $this->assertSame('pr_test_123', $result->id);
        $this->assertSame('payment_request', $result->object);
        $this->assertSame(50000, $result->total);
        $this->assertFalse($result->paid);
        $this->assertSame('Jane Smith', $result->customer_name);
    }

    public function testRetrieve(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new PaymentRequestsResource($client);

        $requestId = 'pr_test_123';
        $expectedResponse = self::createCompletePaymentRequestData([
            'id' => $requestId,
            'paid' => true,
            'paid_at' => 1641081600, // 2022-01-02
            'payment_details' => ChargesResourceTest::createCompleteChargeData([
                'id' => 'ch_test_456',
                'amount' => 50000,
                'status' => 'succeeded',
            ]),
        ]);

        $client->shouldReceive('get')
            ->once()
            ->with("requests/{$requestId}", null, ['base_uri' => 'https://request.magpie.im/api/v1/'])
            ->andReturn($expectedResponse);

        $result = $resource->retrieve($requestId);

        // Test array access (backward compatibility)
        $this->assertSame($requestId, $result['id']);
        $this->assertSame('payment_request', $result['object']);
        $this->assertTrue($result['paid']);
        $this->assertSame(1641081600, $result['paid_at']);
        
        // Test object access (new hybrid API)
        $this->assertSame($requestId, $result->id);
        $this->assertSame('payment_request', $result->object);
        $this->assertTrue($result->paid);
        $this->assertSame(1641081600, $result->paid_at);
    }

    public function testResend(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new PaymentRequestsResource($client);

        $requestId = 'pr_test_123';
        $resentTime = 1641168000; // 2022-01-03

        $expectedResponse = self::createCompletePaymentRequestData([
            'id' => $requestId,
            'updated' => $resentTime,
            'delivered' => [
                'email' => true,
                'sms' => true,
            ],
        ]);
        $expectedResponse['resent_at'] = $resentTime;

        $client->shouldReceive('request')
            ->once()
            ->with('POST', "requests/{$requestId}/resend", null, ['base_uri' => 'https://request.magpie.im/api/v1/'])
            ->andReturn($expectedResponse);

        $result = $resource->resend($requestId);

        // Test array access (backward compatibility)
        $this->assertSame($requestId, $result['id']);
        $this->assertSame('payment_request', $result['object']);
        $this->assertArrayHasKey('resent_at', $result);
        $this->assertSame($resentTime, $result['resent_at']);
        
        // Test object access (new hybrid API)
        $this->assertSame($requestId, $result->id);
        $this->assertSame('payment_request', $result->object);
        $this->assertSame($resentTime, $result->updated);
    }

    public function testVoid(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new PaymentRequestsResource($client);

        $requestId = 'pr_test_123';
        $params = ['reason' => 'duplicate_request'];
        $voidTime = 1641254400; // 2022-01-04

        $expectedResponse = self::createCompletePaymentRequestData([
            'id' => $requestId,
            'voided' => true,
            'voided_at' => $voidTime,
            'void_reason' => 'duplicate_request',
        ]);

        $client->shouldReceive('request')
            ->once()
            ->with('POST', "requests/{$requestId}/void", $params, ['base_uri' => 'https://request.magpie.im/api/v1/'])
            ->andReturn($expectedResponse);

        $result = $resource->void($requestId, $params);

        // Test array access (backward compatibility)
        $this->assertSame($requestId, $result['id']);
        $this->assertSame('payment_request', $result['object']);
        $this->assertTrue($result['voided']);
        $this->assertSame('duplicate_request', $result['void_reason']);
        $this->assertSame($voidTime, $result['voided_at']);
        
        // Test object access (new hybrid API)
        $this->assertSame($requestId, $result->id);
        $this->assertSame('payment_request', $result->object);
        $this->assertTrue($result->voided);
        $this->assertSame('duplicate_request', $result->void_reason);
        $this->assertSame($voidTime, $result->voided_at);
    }

    public function testCreateWithMinimalParams(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new PaymentRequestsResource($client);

        $params = [
            'amount' => 25000,
            'currency' => 'php',
            'description' => 'Invoice #001',
            'recipient' => [
                'email' => 'customer@example.com',
            ],
        ];

        $expectedResponse = self::createCompletePaymentRequestData([
            'id' => 'pr_test_456',
            'subtotal' => 25000,
            'total' => 25000,
            'customer_email' => 'customer@example.com',
            'customer_name' => 'customer@example.com',
            'line_items' => [
                [
                    'name' => 'Invoice #001',
                    'amount' => 25000,
                    'description' => 'Invoice #001',
                    'quantity' => 1,
                    'image' => null,
                ],
            ],
        ]);

        $client->shouldReceive('post')
            ->once()
            ->with('requests', $params, ['base_uri' => 'https://request.magpie.im/api/v1/'])
            ->andReturn($expectedResponse);

        $result = $resource->create($params);

        // Test array access (backward compatibility)
        $this->assertSame('pr_test_456', $result['id']);
        $this->assertSame('payment_request', $result['object']);
        $this->assertSame(25000, $result['total']);
        $this->assertSame('customer@example.com', $result['customer_email']);
        
        // Test object access (new hybrid API)
        $this->assertSame('pr_test_456', $result->id);
        $this->assertSame('payment_request', $result->object);
        $this->assertSame(25000, $result->total);
        $this->assertSame('customer@example.com', $result->customer_email);
    }

    public function testCreateWithOptions(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new PaymentRequestsResource($client);

        $params = [
            'amount' => 75000,
            'currency' => 'php',
            'description' => 'Service Payment',
            'recipient' => [
                'email' => 'test@example.com',
            ],
        ];

        $options = ['idempotency_key' => 'payment_req_123'];

        $expectedResponse = self::createCompletePaymentRequestData([
            'id' => 'pr_test_789',
            'subtotal' => 75000,
            'total' => 75000,
            'customer_email' => 'test@example.com',
            'customer_name' => 'test@example.com',
            'line_items' => [
                [
                    'name' => 'Service Payment',
                    'amount' => 75000,
                    'description' => 'Service Payment',
                    'quantity' => 1,
                    'image' => null,
                ],
            ],
        ]);

        $client->shouldReceive('post')
            ->once()
            ->with('requests', $params, ['idempotency_key' => 'payment_req_123', 'base_uri' => 'https://request.magpie.im/api/v1/'])
            ->andReturn($expectedResponse);

        $result = $resource->create($params, $options);

        // Test array access (backward compatibility)
        $this->assertSame('pr_test_789', $result['id']);
        $this->assertSame('payment_request', $result['object']);
        $this->assertSame(75000, $result['total']);
        
        // Test object access (new hybrid API)
        $this->assertSame('pr_test_789', $result->id);
        $this->assertSame('payment_request', $result->object);
        $this->assertSame(75000, $result->total);
    }

    public function testHandlesValidationErrors(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new PaymentRequestsResource($client);

        $params = [
            'amount' => -1000,  // Invalid negative amount
            'currency' => 'php',
            'description' => 'Invalid Payment',
            'recipient' => [
                'email' => 'invalid-email',  // Invalid email format
            ],
        ];

        $client->shouldReceive('post')
            ->once()
            ->with('requests', $params, ['base_uri' => 'https://request.magpie.im/api/v1/'])
            ->andThrow(new MagpieException('Amount must be positive', 'invalid_request_error'));

        $this->expectException(MagpieException::class);
        $this->expectExceptionMessage('Amount must be positive');

        $resource->create($params);
    }

    public function testUsesCorrectBaseUrl(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new PaymentRequestsResource($client);

        // Verify that the resource is initialized with the correct base URL
        $reflectionClass = new \ReflectionClass($resource);
        $baseUrlProperty = $reflectionClass->getProperty('customBaseUrl');
        $baseUrlProperty->setAccessible(true);

        $this->assertSame('https://request.magpie.im/api/v1/', $baseUrlProperty->getValue($resource));
    }

    public function testVoidWithoutReason(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new PaymentRequestsResource($client);

        $requestId = 'pr_test_123';
        $params = [];  // Empty params
        $voidTime = 1641340800; // 2022-01-05

        $expectedResponse = self::createCompletePaymentRequestData([
            'id' => $requestId,
            'voided' => true,
            'voided_at' => $voidTime,
            'void_reason' => null,
        ]);

        $client->shouldReceive('request')
            ->once()
            ->with('POST', "requests/{$requestId}/void", $params, ['base_uri' => 'https://request.magpie.im/api/v1/'])
            ->andReturn($expectedResponse);

        $result = $resource->void($requestId, $params);

        // Test array access (backward compatibility)
        $this->assertSame($requestId, $result['id']);
        $this->assertSame('payment_request', $result['object']);
        $this->assertTrue($result['voided']);
        $this->assertNull($result['void_reason']);
        
        // Test object access (new hybrid API)
        $this->assertSame($requestId, $result->id);
        $this->assertSame('payment_request', $result->object);
        $this->assertTrue($result->voided);
        $this->assertNull($result->void_reason);
    }
}
