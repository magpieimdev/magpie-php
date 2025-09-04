<?php

declare(strict_types=1);

namespace Magpie\Tests\Unit\Resources;

use Magpie\Exceptions\MagpieException;
use Magpie\Http\Client;
use Magpie\Resources\PaymentRequestsResource;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magpie\Resources\PaymentRequestsResource
 */
class PaymentRequestsResourceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testCreate(): void
    {
        $client = Mockery::mock(Client::class);
        $resource = new PaymentRequestsResource($client);
        
        $params = [
            'amount' => 50000,
            'currency' => 'php',
            'description' => 'Monthly Subscription Payment',
            'recipient' => [
                'name' => 'Jane Smith',
                'email' => 'jane@example.com',
                'phone' => '+639151234567'
            ],
            'send_email' => true,
            'send_sms' => true
        ];
        
        $expectedResponse = [
            'id' => 'pr_test_123',
            'object' => 'payment_request',
            'amount' => 50000,
            'currency' => 'php',
            'description' => 'Monthly Subscription Payment',
            'status' => 'pending',
            'url' => 'https://request.magpie.im/pr_test_123',
            'recipient' => [
                'name' => 'Jane Smith',
                'email' => 'jane@example.com',
                'phone' => '+639151234567'
            ]
        ];
        
        $client->shouldReceive('post')
            ->once()
            ->with('requests', $params, [])
            ->andReturn($expectedResponse);
            
        $result = $resource->create($params);
        
        $this->assertSame('pr_test_123', $result['id']);
        $this->assertSame('payment_request', $result['object']);
        $this->assertSame(50000, $result['amount']);
        $this->assertSame('pending', $result['status']);
        $this->assertSame('Jane Smith', $result['recipient']['name']);
    }

    public function testRetrieve(): void
    {
        $client = Mockery::mock(Client::class);
        $resource = new PaymentRequestsResource($client);
        
        $requestId = 'pr_test_123';
        $expectedResponse = [
            'id' => $requestId,
            'object' => 'payment_request',
            'amount' => 50000,
            'currency' => 'php',
            'status' => 'paid',
            'payment_id' => 'ch_test_456'
        ];
        
        $client->shouldReceive('get')
            ->once()
            ->with("requests/{$requestId}", null, [])
            ->andReturn($expectedResponse);
            
        $result = $resource->retrieve($requestId);
        
        $this->assertSame($requestId, $result['id']);
        $this->assertSame('paid', $result['status']);
        $this->assertSame('ch_test_456', $result['payment_id']);
    }

    public function testResend(): void
    {
        $client = Mockery::mock(Client::class);
        $resource = new PaymentRequestsResource($client);
        
        $requestId = 'pr_test_123';
        
        $expectedResponse = [
            'id' => $requestId,
            'object' => 'payment_request',
            'status' => 'pending',
            'resent_at' => time()
        ];
        
        $client->shouldReceive('request')
            ->once()
            ->with('POST', "requests/{$requestId}/resend", null, [])
            ->andReturn($expectedResponse);
            
        $result = $resource->resend($requestId);
        
        $this->assertSame($requestId, $result['id']);
        $this->assertArrayHasKey('resent_at', $result);
    }

    public function testVoid(): void
    {
        $client = Mockery::mock(Client::class);
        $resource = new PaymentRequestsResource($client);
        
        $requestId = 'pr_test_123';
        $params = ['reason' => 'duplicate_request'];
        
        $expectedResponse = [
            'id' => $requestId,
            'object' => 'payment_request',
            'status' => 'canceled',
            'void_reason' => 'duplicate_request'
        ];
        
        $client->shouldReceive('request')
            ->once()
            ->with('POST', "requests/{$requestId}/void", $params, [])
            ->andReturn($expectedResponse);
            
        $result = $resource->void($requestId, $params);
        
        $this->assertSame($requestId, $result['id']);
        $this->assertSame('canceled', $result['status']);
        $this->assertSame('duplicate_request', $result['void_reason']);
    }

    public function testCreateWithMinimalParams(): void
    {
        $client = Mockery::mock(Client::class);
        $resource = new PaymentRequestsResource($client);
        
        $params = [
            'amount' => 25000,
            'currency' => 'php',
            'description' => 'Invoice #001',
            'recipient' => [
                'email' => 'customer@example.com'
            ]
        ];
        
        $expectedResponse = [
            'id' => 'pr_test_456',
            'object' => 'payment_request',
            'amount' => 25000,
            'status' => 'pending'
        ];
        
        $client->shouldReceive('post')
            ->once()
            ->with('requests', $params, [])
            ->andReturn($expectedResponse);
            
        $result = $resource->create($params);
        
        $this->assertSame('pr_test_456', $result['id']);
        $this->assertSame(25000, $result['amount']);
    }

    public function testCreateWithOptions(): void
    {
        $client = Mockery::mock(Client::class);
        $resource = new PaymentRequestsResource($client);
        
        $params = [
            'amount' => 75000,
            'currency' => 'php',
            'description' => 'Service Payment',
            'recipient' => [
                'email' => 'test@example.com'
            ]
        ];
        
        $options = ['idempotency_key' => 'payment_req_123'];
        
        $expectedResponse = [
            'id' => 'pr_test_789',
            'object' => 'payment_request'
        ];
        
        $client->shouldReceive('post')
            ->once()
            ->with('requests', $params, $options)
            ->andReturn($expectedResponse);
            
        $result = $resource->create($params, $options);
        
        $this->assertSame('pr_test_789', $result['id']);
    }

    public function testHandlesValidationErrors(): void
    {
        $client = Mockery::mock(Client::class);
        $resource = new PaymentRequestsResource($client);
        
        $params = [
            'amount' => -1000,  // Invalid negative amount
            'currency' => 'php',
            'description' => 'Invalid Payment',
            'recipient' => [
                'email' => 'invalid-email'  // Invalid email format
            ]
        ];
        
        $client->shouldReceive('post')
            ->once()
            ->with('requests', $params, [])
            ->andThrow(new MagpieException('Amount must be positive', 'invalid_request_error'));
            
        $this->expectException(MagpieException::class);
        $this->expectExceptionMessage('Amount must be positive');
        
        $resource->create($params);
    }

    public function testUsesCorrectBaseUrl(): void
    {
        $client = Mockery::mock(Client::class);
        $resource = new PaymentRequestsResource($client);
        
        // Verify that the resource is initialized with the correct base URL
        $reflectionClass = new \ReflectionClass($resource);
        $baseUrlProperty = $reflectionClass->getProperty('customBaseUrl');
        $baseUrlProperty->setAccessible(true);
        
        $this->assertSame('https://request.magpie.im/api/v1', $baseUrlProperty->getValue($resource));
    }

    public function testVoidWithoutReason(): void
    {
        $client = Mockery::mock(Client::class);
        $resource = new PaymentRequestsResource($client);
        
        $requestId = 'pr_test_123';
        $params = [];  // Empty params
        
        $expectedResponse = [
            'id' => $requestId,
            'object' => 'payment_request',
            'status' => 'canceled'
        ];
        
        $client->shouldReceive('request')
            ->once()
            ->with('POST', "requests/{$requestId}/void", $params, [])
            ->andReturn($expectedResponse);
            
        $result = $resource->void($requestId, $params);
        
        $this->assertSame($requestId, $result['id']);
        $this->assertSame('canceled', $result['status']);
    }
}
