<?php

declare(strict_types=1);

namespace Magpie\Tests\Unit\Resources;

use Magpie\Exceptions\MagpieException;
use Magpie\Http\Client;
use Magpie\Resources\WebhooksResource;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magpie\Resources\WebhooksResource
 */
class WebhooksResourceTest extends TestCase
{
    protected function tearDown(): void
    {
        \Mockery::close();
    }

    public function testVerifySignature(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new WebhooksResource($client);

        $payload = '{"type":"charge.succeeded","data":{"object":{"id":"ch_test_123"}}}';
        $secret = 'whsec_test_secret';

        // Generate a valid signature for testing (without timestamp)
        $expectedSignature = hash_hmac('sha256', $payload, $secret);
        $signature = "v1={$expectedSignature}";

        $result = $resource->verifySignature($payload, $signature, $secret);

        $this->assertTrue($result);
    }

    public function testVerifySignatureWithInvalidSignature(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new WebhooksResource($client);

        $payload = '{"type":"charge.succeeded","data":{"object":{"id":"ch_test_123"}}}';
        $secret = 'whsec_test_secret';
        $invalidSignature = 'v1=invalid_signature_here';

        $result = $resource->verifySignature($payload, $invalidSignature, $secret);

        $this->assertFalse($result);
    }

    public function testVerifySignatureWithCustomConfig(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new WebhooksResource($client);

        $payload = '{"type":"charge.succeeded"}';
        $secret = 'whsec_custom_secret';

        $config = [
            'algorithm' => 'sha256',
            'tolerance' => 600,
            'prefix' => 'v1=',
        ];

        $expectedSignature = hash_hmac('sha256', $payload, $secret);
        $signature = "v1={$expectedSignature}";

        $result = $resource->verifySignature($payload, $signature, $secret, $config);

        $this->assertTrue($result);
    }

    public function testConstructEvent(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new WebhooksResource($client);

        $payload = '{
            "id": "evt_test_webhook",
            "type": "charge.succeeded",
            "data": {
                "object": {
                    "id": "ch_test_123",
                    "amount": 10000
                }
            },
            "created": ' . time() . ',
            "livemode": false
        }';
        $secret = 'whsec_test_secret';

        $expectedSignature = hash_hmac('sha256', $payload, $secret);
        $signature = "v1={$expectedSignature}";

        $event = $resource->constructEvent($payload, $signature, $secret);

        // Test hybrid API - object access
        $this->assertInstanceOf(\Magpie\DTOs\Responses\WebhookEvent::class, $event);
        $this->assertSame('evt_test_webhook', $event->id);
        $this->assertSame(\Magpie\Enums\WebhookEventType::CHARGE_SUCCEEDED, $event->type);
        $this->assertSame('ch_test_123', $event->data['object']['id']);
        $this->assertSame(10000, $event->data['object']['amount']);
        $this->assertFalse($event->livemode);
        
        // Test hybrid API - array access
        $this->assertSame('evt_test_webhook', $event['id']);
        $this->assertSame('charge.succeeded', $event['type']);
        $this->assertSame('ch_test_123', $event['data']['object']['id']);
        $this->assertSame(10000, $event['data']['object']['amount']);
        $this->assertFalse($event['livemode']);
    }

    public function testConstructEventWithInvalidSignature(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new WebhooksResource($client);

        $payload = '{"type":"charge.succeeded","data":{"object":{"id":"ch_test_123"}}}';
        $secret = 'whsec_test_secret';
        $invalidSignature = 'v1=invalid_signature';

        $this->expectException(MagpieException::class);
        $this->expectExceptionMessage('Invalid webhook signature');

        $resource->constructEvent($payload, $invalidSignature, $secret);
    }

    public function testConstructEventWithInvalidJson(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new WebhooksResource($client);

        $payload = '{invalid-json';
        $secret = 'whsec_test_secret';

        $expectedSignature = hash_hmac('sha256', $payload, $secret);
        $signature = "v1={$expectedSignature}";

        $this->expectException(MagpieException::class);
        $this->expectExceptionMessage('Invalid JSON in webhook payload');

        $resource->constructEvent($payload, $signature, $secret);
    }

    public function testGenerateTestSignature(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new WebhooksResource($client);

        $payload = '{"type":"test.event","data":{"object":{}}}';
        $secret = 'test_secret';

        $signature = $resource->generateTestSignature($payload, $secret);

        // Signature should have the format: t=timestamp,v1=hash
        $this->assertMatchesRegularExpression('/^t=\d+,v1=[a-f0-9]{64}$/', $signature);

        // Extract parts and verify signature is valid
        preg_match('/^t=(\d+),v1=([a-f0-9]{64})$/', $signature, $matches);
        $timestamp = $matches[1];
        $hash = $matches[2];

        $expectedHash = hash_hmac('sha256', $timestamp.'.'.$payload, $secret);
        $this->assertSame($expectedHash, $hash);
    }

    public function testGenerateTestSignatureWithCustomAlgorithm(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new WebhooksResource($client);

        $payload = '{"type":"test.event"}';
        $secret = 'test_secret';

        $signature = $resource->generateTestSignature($payload, $secret, 'sha1', 'v2=');

        // Should use SHA1 and custom prefix
        $this->assertMatchesRegularExpression('/^t=\d+,v2=[a-f0-9]{40}$/', $signature);
    }

    public function testVerifySignatureWithTimestamp(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new WebhooksResource($client);

        $payload = '{"type":"charge.succeeded"}';
        $secret = 'whsec_test_secret';
        $timestamp = (string) time();

        $expectedSignature = hash_hmac('sha256', $payload, $secret);
        $signature = "v1={$expectedSignature}";

        $headers = [
            'x-magpie-signature' => $signature,
            'x-magpie-timestamp' => $timestamp,
        ];

        $result = $resource->verifySignatureWithTimestamp($payload, $headers, $secret);

        $this->assertTrue($result);
    }

    public function testVerifySignatureWithTimestampMissingSignature(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new WebhooksResource($client);

        $payload = '{"type":"charge.succeeded"}';
        $secret = 'whsec_test_secret';

        $headers = [
            'x-magpie-timestamp' => (string) time(),
            // Missing signature header
        ];

        $this->expectException(MagpieException::class);
        $this->expectExceptionMessage('Missing signature header: x-magpie-signature');

        $resource->verifySignatureWithTimestamp($payload, $headers, $secret);
    }

    public function testVerifySignatureWithTimestampExpired(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new WebhooksResource($client);

        $payload = '{"type":"charge.succeeded"}';
        $secret = 'whsec_test_secret';
        $oldTimestamp = (string) (time() - 3600); // 1 hour ago

        $expectedSignature = hash_hmac('sha256', $payload, $secret);
        $signature = "v1={$expectedSignature}";

        $headers = [
            'x-magpie-signature' => $signature,
            'x-magpie-timestamp' => $oldTimestamp,
        ];

        $this->expectException(MagpieException::class);
        $this->expectExceptionMessage('Webhook timestamp is outside tolerance window');

        $resource->verifySignatureWithTimestamp($payload, $headers, $secret);
    }

    public function testIsValidTimestamp(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new WebhooksResource($client);

        $currentTime = time();
        $recentTime = $currentTime - 60; // 1 minute ago
        $oldTime = $currentTime - 3600; // 1 hour ago

        $this->assertTrue($resource->isValidTimestamp($recentTime, 300)); // 5 minute tolerance
        $this->assertFalse($resource->isValidTimestamp($oldTime, 300)); // Outside tolerance
        $this->assertTrue($resource->isValidTimestamp($oldTime, 7200)); // Within 2 hour tolerance
    }

    public function testVerifySignatureHandlesEmptyPayload(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new WebhooksResource($client);

        $payload = '';
        $secret = 'whsec_test_secret';

        $expectedSignature = hash_hmac('sha256', $payload, $secret);
        $signature = "v1={$expectedSignature}";

        $result = $resource->verifySignature($payload, $signature, $secret);

        $this->assertTrue($result);
    }

    public function testVerifySignatureWithMalformedSignature(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new WebhooksResource($client);

        $payload = '{"type":"test"}';
        $secret = 'whsec_test_secret';
        $malformedSignature = 'not-a-valid-signature-format';

        $result = $resource->verifySignature($payload, $malformedSignature, $secret);

        $this->assertFalse($result);
    }

    public function testConstructEventWithCustomConfig(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new WebhooksResource($client);

        $payload = '{
            "id": "evt_123",
            "type": "charge.succeeded",
            "data": {
                "object": {
                    "id": "ch_custom_456",
                    "amount": 5000
                }
            },
            "created": ' . time() . ',
            "livemode": true
        }';
        $secret = 'whsec_custom_secret';

        $config = [
            'algorithm' => 'sha256',
            'prefix' => 'v1=',
            'tolerance' => 600,
        ];

        $expectedSignature = hash_hmac('sha256', $payload, $secret);
        $signature = "v1={$expectedSignature}";

        $event = $resource->constructEvent($payload, $signature, $secret, $config);

        // Test hybrid API - object access
        $this->assertInstanceOf(\Magpie\DTOs\Responses\WebhookEvent::class, $event);
        $this->assertSame('evt_123', $event->id);
        $this->assertSame(\Magpie\Enums\WebhookEventType::CHARGE_SUCCEEDED, $event->type);
        $this->assertSame('ch_custom_456', $event->data['object']['id']);
        $this->assertTrue($event->livemode);
        
        // Test hybrid API - array access
        $this->assertSame('evt_123', $event['id']);
        $this->assertSame('charge.succeeded', $event['type']);
        $this->assertSame('ch_custom_456', $event['data']['object']['id']);
        $this->assertTrue($event['livemode']);
    }
}
