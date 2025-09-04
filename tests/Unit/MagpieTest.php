<?php

declare(strict_types=1);

namespace Magpie\Tests\Unit;

use Magpie\Exceptions\ConfigurationException;
use Magpie\Http\Config;
use Magpie\Magpie;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magpie\Magpie
 */
class MagpieTest extends TestCase
{
    public function testCanInstantiateWithValidSecretKey(): void
    {
        $magpie = new Magpie('sk_test_valid_key');
        
        $this->assertInstanceOf(Magpie::class, $magpie);
        $this->assertSame('v2', $magpie->getApiVersion());
        $this->assertSame('https://api.magpie.im', $magpie->getBaseUrl());
    }

    public function testCanInstantiateWithCustomConfig(): void
    {
        $config = new Config([
            'baseUrl' => 'https://custom-api.magpie.im',
            'apiVersion' => 'v1',
            'timeout' => 60
        ]);

        $magpie = new Magpie('sk_test_valid_key', $config);
        
        $this->assertSame('v1', $magpie->getApiVersion());
        $this->assertSame('https://custom-api.magpie.im', $magpie->getBaseUrl());
        $this->assertSame(60, $magpie->getConfig()->timeout);
    }

    public function testCanInstantiateWithArrayConfig(): void
    {
        $magpie = new Magpie('sk_test_valid_key', [
            'baseUrl' => 'https://test-api.magpie.im',
            'timeout' => 45
        ]);
        
        $this->assertSame('https://test-api.magpie.im', $magpie->getBaseUrl());
        $this->assertSame(45, $magpie->getConfig()->timeout);
    }

    public function testThrowsExceptionForEmptySecretKey(): void
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('Secret key is required');
        
        new Magpie('');
    }

    public function testThrowsExceptionForInvalidSecretKey(): void
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('Invalid secret key format. Secret key must start with "sk_"');
        
        new Magpie('invalid_key_format');
    }

    public function testHasAllResourceInstances(): void
    {
        $magpie = new Magpie('sk_test_valid_key');
        
        $this->assertInstanceOf(\Magpie\Resources\ChargesResource::class, $magpie->charges);
        $this->assertInstanceOf(\Magpie\Resources\CustomersResource::class, $magpie->customers);
        $this->assertInstanceOf(\Magpie\Resources\SourcesResource::class, $magpie->sources);
        $this->assertInstanceOf(\Magpie\Resources\CheckoutResource::class, $magpie->checkout);
        $this->assertInstanceOf(\Magpie\Resources\PaymentRequestsResource::class, $magpie->paymentRequests);
        $this->assertInstanceOf(\Magpie\Resources\PaymentLinksResource::class, $magpie->paymentLinks);
        $this->assertInstanceOf(\Magpie\Resources\WebhooksResource::class, $magpie->webhooks);
    }

    public function testCanSetDebugMode(): void
    {
        $magpie = new Magpie('sk_test_valid_key');
        
        $this->assertFalse($magpie->getConfig()->debug);
        
        $magpie->setDebug(true);
        $this->assertTrue($magpie->getConfig()->debug);
    }
}
