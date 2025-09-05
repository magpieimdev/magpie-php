<?php

declare(strict_types=1);

namespace Magpie\Tests\Unit\Resources;

use Magpie\Exceptions\MagpieException;
use Magpie\Http\Client;
use Magpie\Resources\SourcesResource;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magpie\Resources\SourcesResource
 *
 * Note: Create functionality is intentionally not tested here for PCI compliance.
 * Sources should be created through secure client-side SDKs, not server-side.
 */
class SourcesResourceTest extends TestCase
{
    protected function tearDown(): void
    {
        \Mockery::close();
    }

    public function testRetrieveCardSource(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new SourcesResource($client);

        // Mock the PK authentication flow during retrieve call
        $this->mockPKAuthenticationFlow($client);

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
                'brand' => 'visa',
            ],
            'usage' => 'reusable',
            'created' => 1640995200,
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
        // Note: Only last4 is exposed, never full card number for PCI compliance
        $this->assertSame('4242', $result['card']['last4']);
        $this->assertArrayNotHasKey('number', $result['card']);
        $this->assertArrayNotHasKey('cvc', $result['card']);
    }

    /**
     * Mock the PK authentication flow that happens in SourcesResource constructor.
     */
    private function mockPKAuthenticationFlow($client): void
    {
        // Mock the initial getApiKey() call to check if it's a secret key
        $client->shouldReceive('getApiKey')
            ->once()
            ->andReturn('sk_test_secret123');

        // Mock the organization /me endpoint call
        $organizationData = [
            'pk_test_key' => 'pk_test_public123',
            'pk_live_key' => 'pk_live_public456',
        ];

        $client->shouldReceive('get')
            ->once()
            ->with('me', null, [])
            ->andReturn($organizationData);

        // Mock the setApiKey() call to switch to public key
        $client->shouldReceive('setApiKey')
            ->once()
            ->with('pk_test_public123');

        // Mock the final getApiKey() call in ensurePublicKeyAuthentication
        $client->shouldReceive('getApiKey')
            ->andReturn('pk_test_public123');
    }

    public function testRetrieveWithOptions(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new SourcesResource($client);

        // Mock the PK authentication flow during retrieve call
        $this->mockPKAuthenticationFlow($client);

        $sourceId = 'src_test_123';
        $options = ['expand' => ['charges']];

        $expectedResponse = [
            'id' => $sourceId,
            'object' => 'source',
            'type' => 'card',
            'charges' => [
                [
                    'id' => 'ch_123',
                    'amount' => 25000,
                ],
            ],
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

    public function testRetrieveNonExistentSource(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new SourcesResource($client);

        // Mock the PK authentication flow during retrieve call
        $this->mockPKAuthenticationFlow($client);

        $sourceId = 'src_nonexistent_999';

        $client->shouldReceive('get')
            ->once()
            ->with("sources/{$sourceId}", null, [])
            ->andThrow(new MagpieException('Source not found', 'resource_not_found'));

        $this->expectException(MagpieException::class);
        $this->expectExceptionMessage('Source not found');

        $resource->retrieve($sourceId);
    }

    public function testRetrieveGCashSource(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new SourcesResource($client);

        // Mock the PK authentication flow during retrieve call
        $this->mockPKAuthenticationFlow($client);

        $sourceId = 'src_gcash_456';
        $expectedResponse = [
            'id' => $sourceId,
            'object' => 'source',
            'type' => 'gcash',
            'status' => 'verified',
            'amount' => 50000,
            'currency' => 'php',
            'flow' => 'redirect',
            'redirect' => [
                'url' => 'https://gcash.magpie.im/redirect/src_gcash_456',
                'success' => 'https://example.com/success',
                'fail' => 'https://example.com/fail',
            ],
        ];

        $client->shouldReceive('get')
            ->once()
            ->with("sources/{$sourceId}", null, [])
            ->andReturn($expectedResponse);

        $result = $resource->retrieve($sourceId);

        $this->assertSame($sourceId, $result['id']);
        $this->assertSame('gcash', $result['type']);
        $this->assertSame('verified', $result['status']);
        $this->assertSame(50000, $result['amount']);
        $this->assertSame('php', $result['currency']);
    }
}
