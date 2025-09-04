<?php

declare(strict_types=1);

namespace Magpie;

use Magpie\Http\Client;
use Magpie\Http\Config;
use Magpie\Resources\ChargesResource;
use Magpie\Resources\CustomersResource;
use Magpie\Resources\SourcesResource;
use Magpie\Resources\CheckoutResource;
use Magpie\Resources\PaymentRequestsResource;
use Magpie\Resources\PaymentLinksResource;
use Magpie\Resources\WebhooksResource;
use Psr\Log\LoggerInterface;

/**
 * The main Magpie SDK client for interacting with the Magpie Payment API.
 *
 * This class provides access to all Magpie API resources including customers, charges,
 * sources, checkout sessions, and more. It handles authentication, request/response
 * processing, error handling, and retry logic automatically.
 *
 * @example
 * ```php
 * use Magpie\Magpie;
 *
 * $magpie = new Magpie('your_secret_key');
 *
 * // Create a customer
 * $customer = $magpie->customers->create([
 *     'name' => 'John Doe',
 *     'email' => 'john@example.com'
 * ]);
 *
 * // Create a charge
 * $charge = $magpie->charges->create([
 *     'amount' => 10000,
 *     'currency' => 'php',
 *     'source' => $source['id'],
 *     'description' => 'Payment for Order #1234'
 * ]);
 * ```
 */
class Magpie
{
    /**
     * The HTTP client for API communication.
     */
    private Client $client;

    /**
     * API resource for managing customers.
     */
    public readonly ChargesResource $charges;

    /**
     * API resource for managing customers.
     */
    public readonly CustomersResource $customers;

    /**
     * API resource for managing payment sources (cards, bank accounts, etc.).
     */
    public readonly SourcesResource $sources;

    /**
     * API resource for managing checkout sessions.
     */
    public readonly CheckoutResource $checkout;

    /**
     * API resource for managing payment requests.
     */
    public readonly PaymentRequestsResource $paymentRequests;

    /**
     * API resource for managing payment links.
     */
    public readonly PaymentLinksResource $paymentLinks;

    /**
     * API resource for managing webhooks.
     */
    public readonly WebhooksResource $webhooks;

    /**
     * Create a new Magpie SDK client instance.
     *
     * @param string $secretKey Your Magpie secret API key (must start with 'sk_')
     * @param Config|array|null $config Optional configuration settings for the client
     * @param LoggerInterface|null $logger Optional logger for debug information
     *
     * @throws \Magpie\Exceptions\ConfigurationException When secretKey is invalid
     *
     * @example
     * ```php
     * // Basic usage
     * $magpie = new Magpie('your_secret_key');
     *
     * // With custom configuration
     * $magpie = new Magpie('your_secret_key', [
     *     'timeout' => 10,
     *     'maxRetries' => 5,
     *     'debug' => true
     * ]);
     *
     * // With Config object
     * $config = new \Magpie\Http\Config([
     *     'baseUrl' => 'https://api.magpie.im',
     *     'timeout' => 30,
     *     'debug' => true
     * ]);
     * $magpie = new Magpie('your_secret_key', $config);
     * ```
     */
    public function __construct(
        string $secretKey,
        Config|array|null $config = null,
        ?LoggerInterface $logger = null
    ) {
        $this->client = new Client($secretKey, $config, $logger);

        // Initialize all resource classes
        $this->charges = new ChargesResource($this->client);
        $this->customers = new CustomersResource($this->client);
        $this->sources = new SourcesResource($this->client);
        $this->checkout = new CheckoutResource($this->client);
        $this->paymentRequests = new PaymentRequestsResource($this->client);
        $this->paymentLinks = new PaymentLinksResource($this->client);
        $this->webhooks = new WebhooksResource($this->client);
    }

    /**
     * Get the HTTP client instance.
     *
     * This provides access to the underlying HTTP client for advanced usage
     * or custom API calls not covered by the resource classes.
     *
     * @return Client
     *
     * @example
     * ```php
     * $client = $magpie->getClient();
     * $response = $client->get('/custom-endpoint');
     * ```
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * Get the current configuration.
     *
     * @return Config
     *
     * @example
     * ```php
     * $config = $magpie->getConfig();
     * echo $config->timeout; // 30
     * echo $config->maxRetries; // 3
     * ```
     */
    public function getConfig(): Config
    {
        return $this->client->getConfig();
    }

    /**
     * Test connectivity to the Magpie API.
     *
     * This method sends a lightweight request to verify that the client can
     * successfully communicate with the API using the provided credentials.
     *
     * @return bool True if the connection is successful, false otherwise
     *
     * @example
     * ```php
     * if ($magpie->ping()) {
     *     echo '✅ Connected to Magpie API';
     * } else {
     *     echo '❌ Failed to connect to Magpie API';
     * }
     * ```
     */
    public function ping(): bool
    {
        return $this->client->ping();
    }

    /**
     * Enable or disable debug mode.
     *
     * When debug mode is enabled, detailed request/response information
     * will be logged for troubleshooting purposes.
     *
     * @param bool $debug Whether to enable debug mode
     *
     * @example
     * ```php
     * $magpie->setDebug(true); // Enable detailed logging
     * ```
     */
    public function setDebug(bool $debug): void
    {
        $this->client->getConfig()->debug = $debug;
    }

    /**
     * Get the API version being used.
     *
     * @return string
     */
    public function getApiVersion(): string
    {
        return $this->client->getConfig()->apiVersion;
    }

    /**
     * Get the base URL being used for API requests.
     *
     * @return string
     */
    public function getBaseUrl(): string
    {
        return $this->client->getConfig()->baseUrl;
    }
}
