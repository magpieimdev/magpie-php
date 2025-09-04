<?php

declare(strict_types=1);

namespace Magpie\Resources;

use Magpie\Exceptions\MagpieException;
use Magpie\Http\Client;

/**
 * Webhooks resource for verifying and handling webhook events.
 *
 * This class provides methods to verify webhook signatures and construct
 * verified webhook events from incoming HTTP requests.
 */
class WebhooksResource extends BaseResource
{
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
     * Verify a webhook signature.
     *
     * @param string $payload Raw webhook payload (as received from Magpie)
     * @param string $signature Signature header value
     * @param string $secret Your webhook endpoint secret
     * @param array $config Optional configuration for signature verification
     * @return bool True if the signature is valid
     */
    public function verifySignature(
        string $payload,
        string $signature,
        string $secret,
        array $config = []
    ): bool {
        $tolerance = $config['tolerance'] ?? 300; // 5 minutes default
        
        // Extract timestamp and signature from header
        $elements = explode(',', $signature);
        $timestamp = null;
        $signatures = [];
        
        foreach ($elements as $element) {
            $parts = explode('=', $element, 2);
            if (count($parts) !== 2) {
                continue;
            }
            
            [$prefix, $value] = $parts;
            if ($prefix === 't') {
                $timestamp = (int) $value;
            } elseif ($prefix === 'v1') {
                $signatures[] = $value;
            }
        }
        
        if ($timestamp === null || empty($signatures)) {
            return false;
        }
        
        // Check timestamp tolerance
        $currentTime = time();
        if (abs($currentTime - $timestamp) > $tolerance) {
            return false;
        }
        
        // Verify signature
        $expectedSignature = hash_hmac('sha256', $timestamp . '.' . $payload, $secret);
        
        foreach ($signatures as $signature) {
            if (hash_equals($expectedSignature, $signature)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Construct a webhook event from a verified payload.
     *
     * @param string $payload Raw webhook payload
     * @param string $signature Signature header value  
     * @param string $secret Your webhook endpoint secret
     * @param array $config Optional configuration
     * @return array Parsed and verified webhook event
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
    ): array {
        if (!$this->verifySignature($payload, $signature, $secret, $config)) {
            throw new MagpieException('Invalid webhook signature', 'webhook_error');
        }
        
        try {
            $event = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new MagpieException('Invalid JSON in webhook payload', 'webhook_error', previous: $e);
        }
        
        return $event;
    }

    /**
     * Generate a test webhook signature (for testing purposes).
     *
     * @param string $payload Payload to generate signature for
     * @param string $secret Webhook secret
     * @param string $algorithm Hashing algorithm (default: 'sha256')
     * @param string $prefix Signature prefix (default: 'v1=')
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
        $signature = hash_hmac($algorithm, $timestamp . '.' . $payload, $secret);
        
        return "t={$timestamp},{$prefix}{$signature}";
    }

    /**
     * Validate if a timestamp is within acceptable tolerance.
     *
     * @param int $timestamp Unix timestamp to validate
     * @param int $tolerance Maximum age in seconds (default: 300 = 5 minutes)
     * @return bool True if timestamp is valid
     */
    public function isValidTimestamp(int $timestamp, int $tolerance = 300): bool
    {
        $currentTime = time();
        return abs($currentTime - $timestamp) <= $tolerance;
    }
}
