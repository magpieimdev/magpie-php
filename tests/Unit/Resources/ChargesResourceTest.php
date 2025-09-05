<?php

declare(strict_types=1);

namespace Magpie\Tests\Unit\Resources;

use Magpie\Exceptions\MagpieException;
use Magpie\Http\Client;
use Magpie\Resources\ChargesResource;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magpie\Resources\ChargesResource
 * @covers \Magpie\Resources\BaseResource
 * @covers \Magpie\Exceptions\MagpieException
 */
class ChargesResourceTest extends TestCase
{
    protected function tearDown(): void
    {
        \Mockery::close();
    }

    public function testCreate(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new ChargesResource($client);

        $params = [
            'amount' => 50000,
            'currency' => 'php',
            'source' => 'src_test_123',
            'description' => 'Payment for order #1001',
            'capture' => true,
            'metadata' => [
                'order_id' => '1001',
                'customer_id' => 'cust_123',
            ],
        ];

        $expectedResponse = [
            'id' => 'ch_test_123',
            'object' => 'charge',
            'amount' => 50000,
            'currency' => 'php',
            'source' => [
                'id' => 'src_test_123',
                'type' => 'card',
            ],
            'description' => 'Payment for order #1001',
            'status' => 'succeeded',
            'captured' => true,
            'created' => 1640995200,
            'metadata' => [
                'order_id' => '1001',
                'customer_id' => 'cust_123',
            ],
        ];

        $client->shouldReceive('post')
            ->once()
            ->with('charges', $params, [])
            ->andReturn($expectedResponse);

        $result = $resource->create($params);

        $this->assertSame('ch_test_123', $result['id']);
        $this->assertSame('charge', $result['object']);
        $this->assertSame(50000, $result['amount']);
        $this->assertSame('php', $result['currency']);
        $this->assertSame('succeeded', $result['status']);
        $this->assertTrue($result['captured']);
        $this->assertSame('Payment for order #1001', $result['description']);
    }

    public function testCreateAuthorizeOnly(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new ChargesResource($client);

        $params = [
            'amount' => 30000,
            'currency' => 'php',
            'source' => 'src_card_456',
            'description' => 'Authorization for reservation',
            'capture' => false,  // Authorize only
        ];

        $expectedResponse = [
            'id' => 'ch_auth_456',
            'object' => 'charge',
            'amount' => 30000,
            'currency' => 'php',
            'status' => 'authorized',
            'captured' => false,
            'description' => 'Authorization for reservation',
        ];

        $client->shouldReceive('post')
            ->once()
            ->with('charges', $params, [])
            ->andReturn($expectedResponse);

        $result = $resource->create($params);

        $this->assertSame('ch_auth_456', $result['id']);
        $this->assertSame('authorized', $result['status']);
        $this->assertFalse($result['captured']);
    }

    public function testRetrieve(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new ChargesResource($client);

        $chargeId = 'ch_test_123';
        $expectedResponse = [
            'id' => $chargeId,
            'object' => 'charge',
            'amount' => 50000,
            'currency' => 'php',
            'status' => 'succeeded',
            'captured' => true,
            'source' => [
                'id' => 'src_test_123',
                'type' => 'card',
                'last4' => '4242',
            ],
            'refunds' => [],
        ];

        $client->shouldReceive('get')
            ->once()
            ->with("charges/{$chargeId}", null, [])
            ->andReturn($expectedResponse);

        $result = $resource->retrieve($chargeId);

        $this->assertSame($chargeId, $result['id']);
        $this->assertSame('succeeded', $result['status']);
        $this->assertSame('4242', $result['source']['last4']);
        $this->assertEmpty($result['refunds']);
    }

    public function testCapture(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new ChargesResource($client);

        $chargeId = 'ch_auth_123';
        $params = ['amount' => 25000];

        $expectedResponse = [
            'id' => $chargeId,
            'object' => 'charge',
            'amount' => 30000,
            'amount_captured' => 25000,
            'status' => 'succeeded',
            'captured' => true,
        ];

        $client->shouldReceive('request')
            ->once()
            ->with('POST', "charges/{$chargeId}/capture", $params, [])
            ->andReturn($expectedResponse);

        $result = $resource->capture($chargeId, $params);

        $this->assertSame($chargeId, $result['id']);
        $this->assertSame(25000, $result['amount_captured']);
        $this->assertTrue($result['captured']);
        $this->assertSame('succeeded', $result['status']);
    }

    public function testVerify(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new ChargesResource($client);

        $chargeId = 'ch_verify_123';
        $params = [
            'confirmation_id' => '1234567890',
            'otp' => '123456',
        ];

        $expectedResponse = [
            'id' => $chargeId,
            'object' => 'charge',
            'amount' => 40000,
            'status' => 'succeeded',
            'verification' => [
                'status' => 'verified',
                'verified_at' => 1640995200,
            ],
        ];

        $client->shouldReceive('request')
            ->once()
            ->with('POST', "charges/{$chargeId}/verify", $params, [])
            ->andReturn($expectedResponse);

        $result = $resource->verify($chargeId, $params);

        $this->assertSame($chargeId, $result['id']);
        $this->assertSame('succeeded', $result['status']);
        $this->assertSame('verified', $result['verification']['status']);
    }

    public function testVoid(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new ChargesResource($client);

        $chargeId = 'ch_auth_456';

        $expectedResponse = [
            'id' => $chargeId,
            'object' => 'charge',
            'amount' => 30000,
            'status' => 'canceled',
            'captured' => false,
            'voided_at' => 1640995200,
        ];

        $client->shouldReceive('request')
            ->once()
            ->with('POST', "charges/{$chargeId}/void", null, [])
            ->andReturn($expectedResponse);

        $result = $resource->void($chargeId);

        $this->assertSame($chargeId, $result['id']);
        $this->assertSame('canceled', $result['status']);
        $this->assertFalse($result['captured']);
        $this->assertArrayHasKey('voided_at', $result);
    }

    public function testRefundFull(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new ChargesResource($client);

        $chargeId = 'ch_test_123';
        $params = [
            'amount' => 50000,
            'reason' => 'requested_by_customer',
        ];

        $expectedResponse = [
            'id' => $chargeId,
            'object' => 'charge',
            'amount' => 50000,
            'amount_refunded' => 50000,
            'status' => 'succeeded',
            'refunded' => true,
            'refunds' => [
                [
                    'id' => 'ref_123',
                    'amount' => 50000,
                    'reason' => 'requested_by_customer',
                    'status' => 'succeeded',
                ],
            ],
        ];

        $client->shouldReceive('request')
            ->once()
            ->with('POST', "charges/{$chargeId}/refund", $params, [])
            ->andReturn($expectedResponse);

        $result = $resource->refund($chargeId, $params);

        $this->assertSame($chargeId, $result['id']);
        $this->assertSame(50000, $result['amount_refunded']);
        $this->assertTrue($result['refunded']);
        $this->assertCount(1, $result['refunds']);
        $this->assertSame('ref_123', $result['refunds'][0]['id']);
    }

    public function testRefundPartial(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new ChargesResource($client);

        $chargeId = 'ch_test_456';
        $params = [
            'amount' => 25000,  // Partial refund
            'reason' => 'duplicate',
        ];

        $expectedResponse = [
            'id' => $chargeId,
            'object' => 'charge',
            'amount' => 50000,
            'amount_refunded' => 25000,
            'status' => 'succeeded',
            'refunded' => false,  // Not fully refunded
            'refunds' => [
                [
                    'id' => 'ref_456',
                    'amount' => 25000,
                    'reason' => 'duplicate',
                ],
            ],
        ];

        $client->shouldReceive('request')
            ->once()
            ->with('POST', "charges/{$chargeId}/refund", $params, [])
            ->andReturn($expectedResponse);

        $result = $resource->refund($chargeId, $params);

        $this->assertSame(50000, $result['amount']);
        $this->assertSame(25000, $result['amount_refunded']);
        $this->assertFalse($result['refunded']);  // Not fully refunded
    }

    public function testCreateWithOptions(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new ChargesResource($client);

        $params = [
            'amount' => 100000,
            'currency' => 'php',
            'source' => 'src_test_789',
        ];

        $options = ['idempotency_key' => 'charge_create_123'];

        $expectedResponse = [
            'id' => 'ch_test_789',
            'object' => 'charge',
            'amount' => 100000,
        ];

        $client->shouldReceive('post')
            ->once()
            ->with('charges', $params, $options)
            ->andReturn($expectedResponse);

        $result = $resource->create($params, $options);

        $this->assertSame('ch_test_789', $result['id']);
    }

    public function testRetrieveWithOptions(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new ChargesResource($client);

        $chargeId = 'ch_test_123';
        $options = ['expand' => ['source', 'refunds']];

        $expectedResponse = [
            'id' => $chargeId,
            'source' => [
                'id' => 'src_123',
                'type' => 'card',
                'card' => [
                    'brand' => 'visa',
                    'last4' => '4242',
                ],
            ],
            'refunds' => [
                [
                    'id' => 'ref_123',
                    'amount' => 10000,
                ],
            ],
        ];

        $client->shouldReceive('get')
            ->once()
            ->with("charges/{$chargeId}", null, $options)
            ->andReturn($expectedResponse);

        $result = $resource->retrieve($chargeId, $options);

        $this->assertArrayHasKey('source', $result);
        $this->assertArrayHasKey('card', $result['source']);
        $this->assertArrayHasKey('refunds', $result);
    }

    public function testHandlesCreateError(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new ChargesResource($client);

        $params = [
            'amount' => 50000,
            'currency' => 'php',
            'source' => 'src_invalid_123',  // Invalid source
        ];

        $client->shouldReceive('post')
            ->once()
            ->with('charges', $params, [])
            ->andThrow(new MagpieException('Invalid payment source', 'invalid_request_error'));

        $this->expectException(MagpieException::class);
        $this->expectExceptionMessage('Invalid payment source');

        $resource->create($params);
    }

    public function testHandlesCaptureError(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new ChargesResource($client);

        $chargeId = 'ch_already_captured';
        $params = ['amount' => 25000];

        $client->shouldReceive('request')
            ->once()
            ->with('POST', "charges/{$chargeId}/capture", $params, [])
            ->andThrow(new MagpieException('Charge already captured', 'charge_already_captured'));

        $this->expectException(MagpieException::class);
        $this->expectExceptionMessage('Charge already captured');

        $resource->capture($chargeId, $params);
    }

    public function testCaptureWithOptions(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new ChargesResource($client);

        $chargeId = 'ch_auth_123';
        $params = ['amount' => 30000];
        $options = ['expand' => ['source']];

        $expectedResponse = [
            'id' => $chargeId,
            'amount_captured' => 30000,
            'source' => [
                'id' => 'src_123',
                'type' => 'card',
            ],
        ];

        $client->shouldReceive('request')
            ->once()
            ->with('POST', "charges/{$chargeId}/capture", $params, $options)
            ->andReturn($expectedResponse);

        $result = $resource->capture($chargeId, $params, $options);

        $this->assertSame(30000, $result['amount_captured']);
        $this->assertArrayHasKey('source', $result);
    }

    public function testRefundWithOptions(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new ChargesResource($client);

        $chargeId = 'ch_test_123';
        $params = ['amount' => 20000];
        $options = ['expand' => ['refunds.charges']];

        $expectedResponse = [
            'id' => $chargeId,
            'amount_refunded' => 20000,
            'refunds' => [
                [
                    'id' => 'ref_123',
                    'amount' => 20000,
                    'charges' => [],
                ],
            ],
        ];

        $client->shouldReceive('request')
            ->once()
            ->with('POST', "charges/{$chargeId}/refund", $params, $options)
            ->andReturn($expectedResponse);

        $result = $resource->refund($chargeId, $params, $options);

        $this->assertSame(20000, $result['amount_refunded']);
        $this->assertArrayHasKey('charges', $result['refunds'][0]);
    }

    public function testVerifyWithOptions(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new ChargesResource($client);

        $chargeId = 'ch_verify_456';
        $params = ['confirmation_id' => '987654321'];
        $options = ['expand' => ['source']];

        $expectedResponse = [
            'id' => $chargeId,
            'status' => 'succeeded',
            'source' => [
                'id' => 'src_456',
                'type' => 'bank_debit',
            ],
        ];

        $client->shouldReceive('request')
            ->once()
            ->with('POST', "charges/{$chargeId}/verify", $params, $options)
            ->andReturn($expectedResponse);

        $result = $resource->verify($chargeId, $params, $options);

        $this->assertSame('succeeded', $result['status']);
        $this->assertArrayHasKey('source', $result);
    }
}
