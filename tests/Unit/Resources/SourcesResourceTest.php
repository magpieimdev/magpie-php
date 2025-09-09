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

    public static function createCompleteSourceData(array $overrides = []): array
    {
        $defaults = [
            'id' => 'src_test_123',
            'object' => 'source',
            'type' => 'card',
            'redirect' => [
                'success' => 'https://example.com/success',
                'fail' => 'https://example.com/fail',
            ],
            'vaulted' => false,
            'used' => false,
            'livemode' => false,
            'created_at' => '2022-01-01T00:00:00Z',
            'updated_at' => '2022-01-01T00:00:00Z',
            'metadata' => [],
            'card' => null,
            'bank_account' => null,
            'owner' => null,
        ];

        return array_merge($defaults, $overrides);
    }

    public function testRetrieveCardSource(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new SourcesResource($client);

        // Mock the PK authentication flow during retrieve call
        $this->mockPKAuthenticationFlow($client);

        $sourceId = 'src_test_123';
        $expectedResponse = self::createCompleteSourceData([
            'id' => $sourceId,
            'type' => 'card',
            'card' => [
                'id' => 'card_test_123',
                'object' => 'card',
                'name' => 'John Doe',
                'last4' => '4242',
                'exp_month' => '12',
                'exp_year' => '2025',
                'brand' => 'visa',
                'country' => 'US',
                'cvc_checked' => 'pass',
                'funding' => 'credit',
                'issuing_bank' => 'Test Bank',
            ],
        ]);

        $client->shouldReceive('get')
            ->once()
            ->with("sources/{$sourceId}", null, [])
            ->andReturn($expectedResponse);

        $result = $resource->retrieve($sourceId);

        // Test array access (backward compatibility)
        $this->assertSame($sourceId, $result['id']);
        $this->assertSame('source', $result['object']);
        $this->assertSame('card', $result['type']);
        $this->assertSame('4242', $result['card']['last4']);
        $this->assertSame('visa', $result['card']['brand']);

        // Test object access (new hybrid API)
        $this->assertSame($sourceId, $result->id);
        $this->assertSame('source', $result->object);
        $this->assertSame('card', $result->type->value);
        $this->assertSame('4242', $result->card->last4);
        $this->assertSame('visa', $result->card->brand);

        // Note: Only last4 is exposed, never full card number for PCI compliance
        $this->assertArrayNotHasKey('number', $result['card']);
        $this->assertArrayNotHasKey('cvc', $result['card']);
    }

    /**
     * Mock the PK authentication flow that happens in SourcesResource constructor.
     * Reuses OrganizationResourceTest mock data for consistency.
     */
    private function mockPKAuthenticationFlow($client): void
    {
        // Mock the initial getApiKey() call to check if it's a secret key
        $client->shouldReceive('getApiKey')
            ->once()
            ->andReturn('sk_test_secret123');

        // Reuse complete organization data from OrganizationResourceTest with custom keys
        $organizationData = OrganizationResourceTest::createCompleteOrganizationData([
            'pk_test_key' => 'pk_test_public123',
            'sk_test_key' => 'sk_test_secret123',
            'pk_live_key' => 'pk_live_public456',
            'sk_live_key' => 'sk_live_secret456',
        ]);

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

        $expectedResponse = self::createCompleteSourceData([
            'id' => $sourceId,
            'type' => 'card',
            'charges' => [
                [
                    'id' => 'ch_123',
                    'amount' => 25000,
                ],
            ],
        ]);

        $client->shouldReceive('get')
            ->once()
            ->with("sources/{$sourceId}", null, $options)
            ->andReturn($expectedResponse);

        $result = $resource->retrieve($sourceId, $options);

        // Test array access (backward compatibility)
        $this->assertSame($sourceId, $result['id']);
        $this->assertSame('source', $result['object']);
        $this->assertSame('card', $result['type']);
        $this->assertArrayHasKey('charges', $result);
        $this->assertSame('ch_123', $result['charges'][0]['id']);

        // Test object access (new hybrid API)
        $this->assertSame($sourceId, $result->id);
        $this->assertSame('source', $result->object);
        $this->assertSame('card', $result->type->value);
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
        $expectedResponse = self::createCompleteSourceData([
            'id' => $sourceId,
            'type' => 'gcash',
            'redirect' => [
                'url' => 'https://gcash.magpie.im/redirect/src_gcash_456',
                'success' => 'https://example.com/success',
                'fail' => 'https://example.com/fail',
            ],
            'amount' => 50000,
            'currency' => 'php',
        ]);

        $client->shouldReceive('get')
            ->once()
            ->with("sources/{$sourceId}", null, [])
            ->andReturn($expectedResponse);

        $result = $resource->retrieve($sourceId);

        // Test array access (backward compatibility)
        $this->assertSame($sourceId, $result['id']);
        $this->assertSame('source', $result['object']);
        $this->assertSame('gcash', $result['type']);
        $this->assertSame('https://example.com/success', $result['redirect']['success']);
        $this->assertSame('https://example.com/fail', $result['redirect']['fail']);

        // Test object access (new hybrid API)
        $this->assertSame($sourceId, $result->id);
        $this->assertSame('source', $result->object);
        $this->assertSame('gcash', $result->type->value);
        $this->assertSame('https://example.com/success', $result->redirect->success);
        $this->assertSame('https://example.com/fail', $result->redirect->fail);
    }
}
