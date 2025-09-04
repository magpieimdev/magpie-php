<?php

declare(strict_types=1);

namespace Magpie\Tests\Unit\Resources;

use Magpie\Exceptions\MagpieException;
use Magpie\Http\Client;
use Magpie\Resources\SourcesResource;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magpie\Resources\SourcesResource
 */
class SourcesResourceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testCreateCardSource(): void
    {
        $client = Mockery::mock(Client::class);
        $resource = new SourcesResource($client);
        
        $params = [
            'type' => 'card',
            'card' => [
                'name' => 'John Doe',
                'number' => '4242424242424242',
                'exp_month' => '12',
                'exp_year' => '2025',
                'cvc' => '123'
            ],
            'redirect' => [
                'success' => 'https://example.com/success',
                'fail' => 'https://example.com/fail'
            ]
        ];
        
        $expectedResponse = [
            'id' => 'src_test_123',
            'object' => 'source',
            'type' => 'card',
            'status' => 'pending',
            'flow' => 'redirect',
            'card' => [
                'name' => 'John Doe',
                'last4' => '4242',
                'exp_month' => '12',
                'exp_year' => '2025',
                'brand' => 'visa'
            ],
            'redirect' => [
                'url' => 'https://pay.magpie.im/redirect/src_test_123',
                'success' => 'https://example.com/success',
                'fail' => 'https://example.com/fail'
            ],
            'created' => 1640995200
        ];
        
        $client->shouldReceive('post')
            ->once()
            ->with('sources', $params, [])
            ->andReturn($expectedResponse);
            
        $result = $resource->create($params);
        
        $this->assertSame('src_test_123', $result['id']);
        $this->assertSame('source', $result['object']);
        $this->assertSame('card', $result['type']);
        $this->assertSame('pending', $result['status']);
        $this->assertSame('John Doe', $result['card']['name']);
        $this->assertSame('4242', $result['card']['last4']);
        $this->assertSame('visa', $result['card']['brand']);
    }

    public function testCreateGCashSource(): void
    {
        $client = Mockery::mock(Client::class);
        $resource = new SourcesResource($client);
        
        $params = [
            'type' => 'gcash',
            'amount' => 50000,
            'currency' => 'php',
            'redirect' => [
                'success' => 'https://example.com/success',
                'fail' => 'https://example.com/fail'
            ]
        ];
        
        $expectedResponse = [
            'id' => 'src_gcash_456',
            'object' => 'source',
            'type' => 'gcash',
            'status' => 'pending',
            'flow' => 'redirect',
            'amount' => 50000,
            'currency' => 'php',
            'redirect' => [
                'url' => 'https://gcash.magpie.im/redirect/src_gcash_456',
                'success' => 'https://example.com/success',
                'fail' => 'https://example.com/fail'
            ]
        ];
        
        $client->shouldReceive('post')
            ->once()
            ->with('sources', $params, [])
            ->andReturn($expectedResponse);
            
        $result = $resource->create($params);
        
        $this->assertSame('src_gcash_456', $result['id']);
        $this->assertSame('gcash', $result['type']);
        $this->assertSame('pending', $result['status']);
        $this->assertSame(50000, $result['amount']);
        $this->assertSame('php', $result['currency']);
    }

    public function testCreatePayMayaSource(): void
    {
        $client = Mockery::mock(Client::class);
        $resource = new SourcesResource($client);
        
        $params = [
            'type' => 'paymaya',
            'amount' => 75000,
            'currency' => 'php',
            'owner' => [
                'email' => 'customer@example.com'
            ],
            'redirect' => [
                'success' => 'https://example.com/success',
                'fail' => 'https://example.com/fail'
            ]
        ];
        
        $expectedResponse = [
            'id' => 'src_paymaya_789',
            'object' => 'source',
            'type' => 'paymaya',
            'status' => 'pending',
            'amount' => 75000,
            'currency' => 'php',
            'owner' => [
                'email' => 'customer@example.com',
                'verified_email' => false
            ]
        ];
        
        $client->shouldReceive('post')
            ->once()
            ->with('sources', $params, [])
            ->andReturn($expectedResponse);
            
        $result = $resource->create($params);
        
        $this->assertSame('src_paymaya_789', $result['id']);
        $this->assertSame('paymaya', $result['type']);
        $this->assertSame(75000, $result['amount']);
        $this->assertSame('customer@example.com', $result['owner']['email']);
    }

    public function testRetrieve(): void
    {
        $client = Mockery::mock(Client::class);
        $resource = new SourcesResource($client);
        
        $sourceId = 'src_test_123';
        $expectedResponse = [
            'id' => $sourceId,
            'object' => 'source',
            'type' => 'card',
            'status' => 'verified',
            'flow' => 'redirect',
            'card' => [
                'name' => 'John Doe',
                'last4' => '4242',
                'exp_month' => '12',
                'exp_year' => '2025',
                'brand' => 'visa'
            ],
            'usage' => 'reusable'
        ];
        
        $client->shouldReceive('get')
            ->once()
            ->with("sources/{$sourceId}", null, [])
            ->andReturn($expectedResponse);
            
        $result = $resource->retrieve($sourceId);
        
        $this->assertSame($sourceId, $result['id']);
        $this->assertSame('source', $result['object']);
        $this->assertSame('card', $result['type']);
        $this->assertSame('verified', $result['status']);
        $this->assertSame('reusable', $result['usage']);
    }

    public function testCreateWithMinimalParams(): void
    {
        $client = Mockery::mock(Client::class);
        $resource = new SourcesResource($client);
        
        $params = [
            'type' => 'gcash',
            'amount' => 10000,
            'currency' => 'php'
        ];
        
        $expectedResponse = [
            'id' => 'src_minimal_123',
            'object' => 'source',
            'type' => 'gcash',
            'status' => 'pending',
            'amount' => 10000,
            'currency' => 'php'
        ];
        
        $client->shouldReceive('post')
            ->once()
            ->with('sources', $params, [])
            ->andReturn($expectedResponse);
            
        $result = $resource->create($params);
        
        $this->assertSame('src_minimal_123', $result['id']);
        $this->assertSame('gcash', $result['type']);
        $this->assertSame(10000, $result['amount']);
    }

    public function testCreateWithOptions(): void
    {
        $client = Mockery::mock(Client::class);
        $resource = new SourcesResource($client);
        
        $params = [
            'type' => 'card',
            'card' => [
                'name' => 'Jane Doe',
                'number' => '5555555555554444',
                'exp_month' => '06',
                'exp_year' => '2026',
                'cvc' => '456'
            ]
        ];
        
        $options = ['idempotency_key' => 'source_create_123'];
        
        $expectedResponse = [
            'id' => 'src_options_456',
            'object' => 'source',
            'type' => 'card'
        ];
        
        $client->shouldReceive('post')
            ->once()
            ->with('sources', $params, $options)
            ->andReturn($expectedResponse);
            
        $result = $resource->create($params, $options);
        
        $this->assertSame('src_options_456', $result['id']);
    }

    public function testRetrieveWithOptions(): void
    {
        $client = Mockery::mock(Client::class);
        $resource = new SourcesResource($client);
        
        $sourceId = 'src_test_123';
        $options = ['expand' => ['charges']];
        
        $expectedResponse = [
            'id' => $sourceId,
            'object' => 'source',
            'type' => 'card',
            'charges' => [
                [
                    'id' => 'ch_123',
                    'amount' => 25000
                ]
            ]
        ];
        
        $client->shouldReceive('get')
            ->once()
            ->with("sources/{$sourceId}", null, $options)
            ->andReturn($expectedResponse);
            
        $result = $resource->retrieve($sourceId, $options);
        
        $this->assertSame($sourceId, $result['id']);
        $this->assertArrayHasKey('charges', $result);
        $this->assertSame('ch_123', $result['charges'][0]['id']);
    }

    public function testCreateBankTransferSource(): void
    {
        $client = Mockery::mock(Client::class);
        $resource = new SourcesResource($client);
        
        $params = [
            'type' => 'bank_transfer',
            'amount' => 100000,
            'currency' => 'php',
            'owner' => [
                'name' => 'John Doe',
                'email' => 'john@example.com'
            ]
        ];
        
        $expectedResponse = [
            'id' => 'src_bank_123',
            'object' => 'source',
            'type' => 'bank_transfer',
            'status' => 'pending',
            'amount' => 100000,
            'currency' => 'php',
            'owner' => [
                'name' => 'John Doe',
                'email' => 'john@example.com'
            ],
            'bank_transfer' => [
                'bank' => 'BPI',
                'account_number' => '1234567890',
                'account_name' => 'Magpie Philippines Inc'
            ]
        ];
        
        $client->shouldReceive('post')
            ->once()
            ->with('sources', $params, [])
            ->andReturn($expectedResponse);
            
        $result = $resource->create($params);
        
        $this->assertSame('src_bank_123', $result['id']);
        $this->assertSame('bank_transfer', $result['type']);
        $this->assertSame('BPI', $result['bank_transfer']['bank']);
        $this->assertSame('1234567890', $result['bank_transfer']['account_number']);
    }

    public function testHandlesValidationErrors(): void
    {
        $client = Mockery::mock(Client::class);
        $resource = new SourcesResource($client);
        
        $params = [
            'type' => 'card',
            'card' => [
                'name' => 'Invalid Card',
                'number' => '1234567890123456',  // Invalid card number
                'exp_month' => '13',  // Invalid month
                'exp_year' => '2020',  // Expired year
                'cvc' => '12'  // Invalid CVC
            ]
        ];
        
        $client->shouldReceive('post')
            ->once()
            ->with('sources', $params, [])
            ->andThrow(new MagpieException('Invalid card details', 'card_error'));
            
        $this->expectException(MagpieException::class);
        $this->expectExceptionMessage('Invalid card details');
        
        $resource->create($params);
    }

    public function testHandlesUnsupportedSourceType(): void
    {
        $client = Mockery::mock(Client::class);
        $resource = new SourcesResource($client);
        
        $params = [
            'type' => 'unsupported_type',
            'amount' => 25000,
            'currency' => 'php'
        ];
        
        $client->shouldReceive('post')
            ->once()
            ->with('sources', $params, [])
            ->andThrow(new MagpieException('Unsupported source type', 'invalid_request_error'));
            
        $this->expectException(MagpieException::class);
        $this->expectExceptionMessage('Unsupported source type');
        
        $resource->create($params);
    }

    public function testRetrieveNonExistentSource(): void
    {
        $client = Mockery::mock(Client::class);
        $resource = new SourcesResource($client);
        
        $sourceId = 'src_nonexistent_999';
        
        $client->shouldReceive('get')
            ->once()
            ->with("sources/{$sourceId}", null, [])
            ->andThrow(new MagpieException('Source not found', 'resource_not_found'));
            
        $this->expectException(MagpieException::class);
        $this->expectExceptionMessage('Source not found');
        
        $resource->retrieve($sourceId);
    }

    public function testCreateReusableCardSource(): void
    {
        $client = Mockery::mock(Client::class);
        $resource = new SourcesResource($client);
        
        $params = [
            'type' => 'card',
            'card' => [
                'name' => 'Reusable Card',
                'number' => '4000000000000002',
                'exp_month' => '12',
                'exp_year' => '2025',
                'cvc' => '123'
            ],
            'usage' => 'reusable'
        ];
        
        $expectedResponse = [
            'id' => 'src_reusable_123',
            'object' => 'source',
            'type' => 'card',
            'status' => 'verified',
            'usage' => 'reusable',
            'card' => [
                'name' => 'Reusable Card',
                'last4' => '0002',
                'brand' => 'visa'
            ]
        ];
        
        $client->shouldReceive('post')
            ->once()
            ->with('sources', $params, [])
            ->andReturn($expectedResponse);
            
        $result = $resource->create($params);
        
        $this->assertSame('src_reusable_123', $result['id']);
        $this->assertSame('reusable', $result['usage']);
        $this->assertSame('verified', $result['status']);
        $this->assertSame('0002', $result['card']['last4']);
    }
}
