<?php

declare(strict_types=1);

namespace Magpie\Tests\Unit\Resources;

use Magpie\Exceptions\MagpieException;
use Magpie\Http\Client;
use Magpie\Resources\OrganizationResource;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magpie\Resources\OrganizationResource
 */
class OrganizationResourceTest extends TestCase
{
    protected function tearDown(): void
    {
        \Mockery::close();
    }

    /**
     * Create complete organization mock data with all required fields for the Organization DTO.
     */
    public static function createCompleteOrganizationData(array $overrides = []): array
    {
        $defaults = [
            'object' => 'organization',
            'id' => 'org_test_123456789',
            'title' => 'Test Organization',
            'account_name' => 'Test Merchant',
            'statement_descriptor' => 'TEST ORG',
            'pk_test_key' => 'pk_test_abc123def456',
            'sk_test_key' => 'sk_test_xyz789uvw321',
            'pk_live_key' => 'pk_live_ghi789jkl012',
            'sk_live_key' => 'sk_live_mno345pqr678',
            'branding' => [
                'icon' => 'https://example.com/test-icon.png',
                'brand_color' => '#000000',
                'accent_color' => '#ffffff',
            ],
            'status' => 'approved',
            'created_at' => '2022-01-01T00:00:00Z',
            'updated_at' => '2022-01-01T00:00:00Z',
            'payment_method_settings' => [
                'card' => [
                    'status' => 'approved',
                    'rate' => ['mdr' => 0.035, 'fixed_fee' => 500],
                ],
                'gcash' => [
                    'status' => 'approved',
                    'rate' => ['mdr' => 0.025, 'fixed_fee' => 0],
                ],
            ],
            'rates' => [],
            'payout_settings' => [
                'schedule' => 'daily',
                'delivery_type' => 'standard',
                'bank_code' => 'TEST_BANK',
                'account_number' => '1234567890',
            ],
            'metadata' => [],
            'business_address' => null,
        ];

        return array_merge($defaults, $overrides);
    }

    public function testMe(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new OrganizationResource($client);

        $expectedResponse = self::createCompleteOrganizationData();

        $client->shouldReceive('get')
            ->once()
            ->with('me', null, [])
            ->andReturn($expectedResponse);

        $result = $resource->me();

        // Test array access (backward compatibility)
        $this->assertSame('organization', $result['object']);
        $this->assertSame('org_test_123456789', $result['id']);
        $this->assertSame('Test Organization', $result['title']);
        $this->assertSame('Test Merchant', $result['account_name']);
        $this->assertSame('TEST ORG', $result['statement_descriptor']);
        $this->assertSame('approved', $result['status']);
        $this->assertArrayHasKey('pk_test_key', $result);
        $this->assertArrayHasKey('pk_live_key', $result);
        
        // Test object access (new hybrid API)
        $this->assertSame('organization', $result->object);
        $this->assertSame('org_test_123456789', $result->id);
        $this->assertSame('Test Organization', $result->title);
        $this->assertSame('Test Merchant', $result->account_name);
        $this->assertSame('TEST ORG', $result->statement_descriptor);
        $this->assertSame('approved', $result->status);
        $this->assertSame('pk_test_abc123def456', $result->pk_test_key);
        $this->assertSame('pk_live_ghi789jkl012', $result->pk_live_key);
    }

    public function testGetPublicKeyForTestEnvironment(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new OrganizationResource($client);

        $organizationData = [
            'pk_test_key' => 'pk_test_abc123def456',
            'pk_live_key' => 'pk_live_ghi789jkl012',
        ];

        $secretKey = 'sk_test_xyz789uvw321';

        $publicKey = $resource->getPublicKey($organizationData, $secretKey);

        $this->assertSame('pk_test_abc123def456', $publicKey);
    }

    public function testGetPublicKeyForLiveEnvironment(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new OrganizationResource($client);

        $organizationData = [
            'pk_test_key' => 'pk_test_abc123def456',
            'pk_live_key' => 'pk_live_ghi789jkl012',
        ];

        $secretKey = 'sk_live_mno345pqr678';

        $publicKey = $resource->getPublicKey($organizationData, $secretKey);

        $this->assertSame('pk_live_ghi789jkl012', $publicKey);
    }

    public function testGetPublicKeyThrowsExceptionWhenMissing(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new OrganizationResource($client);

        $organizationData = [
            'pk_live_key' => 'pk_live_ghi789jkl012',
        ];

        $secretKey = 'sk_test_xyz789uvw321';

        $this->expectException(MagpieException::class);
        $this->expectExceptionMessage('No test public key available for organization');

        $resource->getPublicKey($organizationData, $secretKey);
    }

    public function testGetPaymentMethodsAll(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new OrganizationResource($client);

        $organizationData = [
            'payment_method_settings' => [
                'card' => ['status' => 'approved'],
                'gcash' => ['status' => 'approved'],
                'bank_transfer' => ['status' => 'disabled'],
            ],
        ];

        $paymentMethods = $resource->getPaymentMethods($organizationData);

        $this->assertCount(3, $paymentMethods);
        $this->assertArrayHasKey('card', $paymentMethods);
        $this->assertArrayHasKey('gcash', $paymentMethods);
        $this->assertArrayHasKey('bank_transfer', $paymentMethods);
    }

    public function testGetPaymentMethodsSpecific(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new OrganizationResource($client);

        $organizationData = [
            'payment_method_settings' => [
                'card' => ['status' => 'approved', 'mdr' => 0.035],
                'gcash' => ['status' => 'approved', 'mdr' => 0.025],
            ],
        ];

        $cardSettings = $resource->getPaymentMethods($organizationData, 'card');

        $this->assertSame('approved', $cardSettings['status']);
        $this->assertSame(0.035, $cardSettings['mdr']);
    }

    public function testIsPaymentMethodEnabled(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new OrganizationResource($client);

        $organizationData = [
            'payment_method_settings' => [
                'card' => ['status' => 'approved'],
                'gcash' => ['status' => 'approved'],
                'bank_transfer' => ['status' => 'disabled'],
                'crypto' => [], // missing status
            ],
        ];

        $this->assertTrue($resource->isPaymentMethodEnabled($organizationData, 'card'));
        $this->assertTrue($resource->isPaymentMethodEnabled($organizationData, 'gcash'));
        $this->assertFalse($resource->isPaymentMethodEnabled($organizationData, 'bank_transfer'));
        $this->assertFalse($resource->isPaymentMethodEnabled($organizationData, 'crypto'));
        $this->assertFalse($resource->isPaymentMethodEnabled($organizationData, 'nonexistent'));
    }

    public function testGetBranding(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new OrganizationResource($client);

        $organizationData = [
            'branding' => [
                'icon' => 'https://example.com/test-icon.png',
                'logo' => 'https://example.com/test-logo.png',
                'use_logo' => true,
                'brand_color' => '#000000',
                'accent_color' => '#ffffff',
            ],
        ];

        $branding = $resource->getBranding($organizationData);

        $this->assertSame('https://example.com/test-icon.png', $branding['icon']);
        $this->assertSame('#000000', $branding['brand_color']);
        $this->assertSame('#ffffff', $branding['accent_color']);
        $this->assertTrue($branding['use_logo']);
    }

    public function testGetPayoutSettings(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new OrganizationResource($client);

        $organizationData = [
            'payout_settings' => [
                'schedule' => 'daily',
                'delivery_type' => 'standard',
                'bank_code' => 'TEST_BANK',
                'account_number' => '1234567890',
                'account_name' => 'Test Merchant Account',
            ],
        ];

        $payoutSettings = $resource->getPayoutSettings($organizationData);

        $this->assertSame('daily', $payoutSettings['schedule']);
        $this->assertSame('standard', $payoutSettings['delivery_type']);
        $this->assertSame('TEST_BANK', $payoutSettings['bank_code']);
        $this->assertSame('1234567890', $payoutSettings['account_number']);
        $this->assertSame('Test Merchant Account', $payoutSettings['account_name']);
    }
}
