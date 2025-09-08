<?php

declare(strict_types=1);

namespace Magpie\Http;

/**
 * Configuration class for the Magpie HTTP client.
 *
 * Holds all configuration options for the HTTP client including
 * timeouts, retry settings, debugging options, and API endpoints.
 */
class Config
{
    /**
     * Base URL for the Magpie API.
     */
    public string $baseUrl = 'https://api.magpie.im';

    /**
     * API version to use.
     */
    public string $apiVersion = 'v2';

    /**
     * Request timeout in seconds.
     */
    public int $timeout = 30;

    /**
     * Connection timeout in seconds.
     */
    public int $connectTimeout = 10;

    /**
     * Maximum number of retry attempts.
     */
    public int $maxRetries = 3;

    /**
     * Base retry delay in milliseconds.
     */
    public int $retryDelay = 1000;

    /**
     * Maximum retry delay in seconds.
     */
    public int $maxRetryDelay = 30;

    /**
     * Whether to enable debug logging.
     */
    public bool $debug = false;

    /**
     * Whether to verify SSL certificates.
     */
    public bool $verifySsl = true;

    /**
     * User agent string for requests.
     */
    public string $userAgent = 'magpie-php/1.0.0';

    /**
     * Additional headers to send with every request.
     */
    public array $defaultHeaders = [];

    /**
     * Create a new Config instance.
     *
     * @param array $options Configuration options
     */
    public function __construct(array $options = [])
    {
        foreach ($options as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }

        $this->userAgent = $this->buildUserAgent();
    }

    /**
     * Get the full API base URL.
     */
    public function getApiUrl(): string
    {
        return rtrim($this->baseUrl, '/').'/'.ltrim($this->apiVersion, '/') . '/';
    }

    /**
     * Build the User-Agent string.
     */
    private function buildUserAgent(): string
    {
        $phpVersion = PHP_VERSION;
        $osName = php_uname('s');
        $osVersion = php_uname('r');

        return "magpie-php/1.0.0 (PHP/{$phpVersion}; {$osName}/{$osVersion})";
    }

    /**
     * Convert configuration to array.
     */
    public function toArray(): array
    {
        return [
            'baseUrl' => $this->baseUrl,
            'apiVersion' => $this->apiVersion,
            'timeout' => $this->timeout,
            'connectTimeout' => $this->connectTimeout,
            'maxRetries' => $this->maxRetries,
            'retryDelay' => $this->retryDelay,
            'maxRetryDelay' => $this->maxRetryDelay,
            'debug' => $this->debug,
            'verifySsl' => $this->verifySsl,
            'userAgent' => $this->userAgent,
            'defaultHeaders' => $this->defaultHeaders,
        ];
    }
}
