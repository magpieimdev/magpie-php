<?php

declare(strict_types=1);

namespace Magpie\Resources;

use Magpie\Contracts\WebhookServiceInterface;
use Magpie\DTOs\Responses\WebhookEvent;
use Magpie\Exceptions\MagpieException;
use Magpie\Http\Client;

/**
 * Webhooks resource for verifying and handling webhook events.
 *
 * This class provides methods to verify webhook signatures and construct
 * verified webhook events from incoming HTTP requests.
 */
class WebhooksResource extends BaseResource implements WebhookServiceInterface
{
    /**
     * Default configuration for webhook signature verification.
     */
    private const DEFAULT_CONFIG = [
        'algorithm' => 'sha256',
        'signatureHeader' => 'x-magpie-signature',
        'timestampHeader' => 'x-magpie-timestamp',
        'tolerance' => 300, // 5 minutes
        'prefix' => 'v1=',
    ];

    /**
     * Create a new WebhooksResource instance.
     *
     * @param Client $client The HTTP client instance for API communication
     */
    public function __construct(Client $client)
    {
        parent::__construct($client, '/webhooks');
    }

    /**
     * Verify a webhook signature using timing-safe comparison.
     *
     * @param string $payload   Raw webhook payload (string)
     * @param string $signature Signature from webhook headers
     * @param string $secret    Your webhook endpoint secret
     * @param array  $config    Optional configuration for signature verification
     *
     * @return bool True if the signature is valid
     *
     * @example
     * ```php
     * $isValid = $magpie->webhooks->verifySignature(
     *     $payload,
     *     $request->header('x-magpie-signature'),
     *     'whsec_...',
     *     ['tolerance' => 600]
     * );
     * ```
     */
    public function verifySignature(
        string $payload,
        string $signature,
        string $secret,
        array $config = []
    ): bool {
        $finalConfig = array_merge(self::DEFAULT_CONFIG, $config);

        try {
            $parsedSignature = $this->parseSignature($signature, $finalConfig['prefix']);
            $expectedSignature = $this->generateSignature($payload, $secret, $finalConfig['algorithm']);

            return $this->timingSafeEqual($parsedSignature['signature'], $expectedSignature);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Construct a webhook event from a verified payload.
     *
     * @param string $payload   Raw webhook payload
     * @param string $signature Signature header value
     * @param string $secret    Your webhook endpoint secret
     * @param array  $config    Optional configuration
     *
     * @return array Parsed and verified webhook event
     *
     * @throws MagpieException When signature verification or JSON parsing fails
     *
     * @example
     * ```php
     * $event = $magpie->webhooks->constructEvent(
     *     $request->getContent(),
     *     $request->header('magpie-signature'),
     *     'whsec_your_secret_here'
     * );
     *
     * switch ($event['type']) {
     *     case 'charge.succeeded':
     *         // Handle successful payment
     *         break;
     *     case 'charge.failed':
     *         // Handle failed payment
     *         break;
     * }
     * ```
     */
    public function constructEvent(
        string $payload,
        string $signature,
        string $secret,
        array $config = []
    ): mixed {
        if (! $this->verifySignature($payload, $signature, $secret, $config)) {
            throw new MagpieException('Invalid webhook signature', 'webhook_error');
        }

        try {
            $event = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new MagpieException('Invalid JSON in webhook payload', 'webhook_error', previous: $e);
        }

        return $this->createFromArray($event);
    }

    /**
     * Generate a test webhook signature (for testing purposes).
     *
     * @param string $payload   Payload to generate signature for
     * @param string $secret    Webhook secret
     * @param string $algorithm Hashing algorithm (default: 'sha256')
     * @param string $prefix    Signature prefix (default: 'v1=')
     *
     * @return string Generated signature string
     *
     * @example
     * ```php
     * // For testing your webhook handler
     * $testPayload = json_encode(['type' => 'test.event', 'data' => ['object' => []]]);
     * $testSignature = $magpie->webhooks->generateTestSignature(
     *     $testPayload,
     *     'your_test_secret'
     * );
     * ```
     */
    public function generateTestSignature(
        string $payload,
        string $secret,
        string $algorithm = 'sha256',
        string $prefix = 'v1='
    ): string {
        $timestamp = time();
        $signature = hash_hmac($algorithm, $timestamp.'.'.$payload, $secret);

        return "t={$timestamp},{$prefix}{$signature}";
    }

    /**
     * Verify webhook signature with timestamp validation.
     *
     * @param string $payload Raw webhook payload
     * @param array  $headers Request headers array
     * @param string $secret  Webhook endpoint secret
     * @param array  $config  Optional configuration
     *
     * @return bool True if both signature and timestamp are valid
     *
     * @throws MagpieException When signature or timestamp validation fails
     */
    public function verifySignatureWithTimestamp(
        string $payload,
        array $headers,
        string $secret,
        array $config = []
    ): bool {
        $finalConfig = array_merge(self::DEFAULT_CONFIG, $config);

        $signature = $this->getHeader($headers, $finalConfig['signatureHeader']);
        $timestamp = $this->getHeader($headers, $finalConfig['timestampHeader']);

        if (! $signature) {
            throw new MagpieException("Missing signature header: {$finalConfig['signatureHeader']}", 'invalid_request_error', 'webhook_signature_missing');
        }

        // Verify timestamp if provided
        if ($timestamp && ! $this->isValidTimestamp((int) $timestamp, $finalConfig['tolerance'])) {
            throw new MagpieException('Webhook timestamp is outside tolerance window', 'invalid_request_error', 'webhook_timestamp_invalid');
        }

        return $this->verifySignature($payload, $signature, $secret, $config);
    }

    /**
     * Validate if a timestamp is within acceptable tolerance.
     *
     * @param int $timestamp Unix timestamp to validate
     * @param int $tolerance Maximum age in seconds (default: 300 = 5 minutes)
     *
     * @return bool True if timestamp is valid
     */
    public function isValidTimestamp(int $timestamp, int $tolerance = 300): bool
    {
        $currentTime = time();

        return abs($currentTime - $timestamp) <= $tolerance;
    }

    /**
     * Generate HMAC signature for payload.
     */
    private function generateSignature(string $payload, string $secret, string $algorithm): string
    {
        return hash_hmac($algorithm, $payload, $secret);
    }

    /**
     * Parse signature header value.
     *
     * @throws \Exception
     */
    private function parseSignature(string $signature, string $prefix): array
    {
        if (! str_starts_with($signature, $prefix)) {
            throw new \Exception("Invalid signature format. Expected prefix: {$prefix}");
        }

        return [
            'version' => rtrim($prefix, '='),
            'signature' => substr($signature, strlen($prefix)),
        ];
    }

    /**
     * Timing-safe string comparison to prevent timing attacks.
     */
    private function timingSafeEqual(string $a, string $b): bool
    {
        if (strlen($a) !== strlen($b)) {
            return false;
        }

        return hash_equals($a, $b);
    }

    /**
     * Get header value handling both string and array cases.
     */
    private function getHeader(array $headers, string $name): ?string
    {
        $value = $headers[$name] ?? $headers[strtolower($name)] ?? null;

        return is_array($value) ? $value[0] : $value;
    }

    protected function createFromArray(array $data): WebhookEvent
    {
        return WebhookEvent::fromArray($data);
    }
}
