<?php

declare(strict_types=1);

namespace Magpie\Http;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Magpie\Exceptions\AuthenticationException;
use Magpie\Exceptions\ConfigurationException;
use Magpie\Exceptions\MagpieException;
use Magpie\Exceptions\NetworkException;
use Magpie\Exceptions\NotFoundException;
use Magpie\Exceptions\PermissionException;
use Magpie\Exceptions\RateLimitException;
use Magpie\Exceptions\ValidationException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * HTTP client for communicating with the Magpie API.
 *
 * This class handles all HTTP communication with the Magpie API, including
 * authentication, request/response processing, error handling, and retry logic.
 * It's built on top of Guzzle HTTP client for reliability and performance.
 */
class Client
{
    /**
     * The secret API key for authentication.
     */
    private string $secretKey;

    /**
     * Client configuration.
     */
    private Config $config;

    /**
     * The underlying Guzzle HTTP client.
     */
    private GuzzleClient $httpClient;

    /**
     * Logger for debug and error information.
     */
    private LoggerInterface $logger;

    /**
     * Create a new HTTP client instance.
     *
     * @param string $secretKey The Magpie secret API key
     * @param Config|array|null $config Client configuration
     * @param LoggerInterface|null $logger Logger for debug information
     *
     * @throws ConfigurationException
     */
    public function __construct(
        string $secretKey,
        Config|array|null $config = null,
        ?LoggerInterface $logger = null
    ) {
        $this->validateSecretKey($secretKey);
        
        $this->secretKey = $secretKey;
        $this->config = $config instanceof Config ? $config : new Config($config ?? []);
        $this->logger = $logger ?? new NullLogger();

        $this->httpClient = $this->createHttpClient();
    }

    /**
     * Validate the secret key format.
     *
     * @param string $secretKey
     * @throws ConfigurationException
     */
    private function validateSecretKey(string $secretKey): void
    {
        if (empty($secretKey)) {
            throw new ConfigurationException('Secret key is required');
        }

        if (!str_starts_with($secretKey, 'sk_')) {
            throw new ConfigurationException('Invalid secret key format. Secret key must start with "sk_"');
        }
    }

    /**
     * Create the underlying Guzzle HTTP client.
     *
     * @return GuzzleClient
     */
    private function createHttpClient(): GuzzleClient
    {
        $stack = HandlerStack::create();
        
        // Add retry middleware
        $stack->push($this->createRetryMiddleware());
        
        // Add request/response logging middleware
        if ($this->config->debug) {
            $stack->push($this->createLoggingMiddleware());
        }

        return new GuzzleClient([
            'base_uri' => $this->config->getApiUrl(),
            'handler' => $stack,
            'timeout' => $this->config->timeout,
            'connect_timeout' => $this->config->connectTimeout,
            'verify' => $this->config->verifySsl,
            'headers' => array_merge([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'User-Agent' => $this->config->userAgent,
                'X-API-Version' => $this->config->apiVersion,
            ], $this->config->defaultHeaders),
            'auth' => [$this->secretKey, ''],
        ]);
    }

    /**
     * Create retry middleware for handling transient failures.
     *
     * @return callable
     */
    private function createRetryMiddleware(): callable
    {
        return Middleware::retry(
            function ($retries, RequestInterface $request, ?ResponseInterface $response = null, ?RequestException $exception = null) {
                // Don't retry if we've hit the max retries
                if ($retries >= $this->config->maxRetries) {
                    return false;
                }

                // Retry on network errors
                if ($exception instanceof ConnectException) {
                    return true;
                }

                // Don't retry if we don't have a response
                if ($response === null) {
                    return false;
                }

                $statusCode = $response->getStatusCode();
                
                // Retry on server errors and rate limiting
                if ($statusCode >= 500 || $statusCode === 429) {
                    return true;
                }

                // Don't retry POST requests without idempotency key
                if ($request->getMethod() === 'POST' && !$request->hasHeader('X-Idempotency-Key')) {
                    return false;
                }

                return false;
            },
            function ($retries) {
                return $this->calculateRetryDelay($retries);
            }
        );
    }

    /**
     * Calculate retry delay with exponential backoff and jitter.
     *
     * @param int $retries
     * @return int Delay in milliseconds
     */
    private function calculateRetryDelay(int $retries): int
    {
        $baseDelay = $this->config->retryDelay;
        $exponentialDelay = $baseDelay * (2 ** ($retries - 1));
        $jitter = rand(0, (int) ($exponentialDelay * 0.1));
        
        return min($exponentialDelay + $jitter, $this->config->maxRetryDelay * 1000);
    }

    /**
     * Create logging middleware for debug output.
     *
     * @return callable
     */
    private function createLoggingMiddleware(): callable
    {
        return Middleware::tap(function (RequestInterface $request) {
            $this->logger->debug('HTTP Request', [
                'method' => $request->getMethod(),
                'uri' => (string) $request->getUri(),
                'headers' => $this->sanitizeHeaders($request->getHeaders()),
                'body' => (string) $request->getBody(),
            ]);
        }, function (RequestInterface $request, array $options, ResponseInterface $response) {
            $this->logger->debug('HTTP Response', [
                'status' => $response->getStatusCode(),
                'headers' => $response->getHeaders(),
                'body' => (string) $response->getBody(),
            ]);
        });
    }

    /**
     * Sanitize headers for logging (remove sensitive information).
     *
     * @param array $headers
     * @return array
     */
    private function sanitizeHeaders(array $headers): array
    {
        $sanitized = [];
        
        foreach ($headers as $name => $values) {
            if (in_array(strtolower($name), ['authorization', 'x-api-key'])) {
                $sanitized[$name] = ['[REDACTED]'];
            } else {
                $sanitized[$name] = $values;
            }
        }
        
        return $sanitized;
    }

    /**
     * Make an HTTP request to the Magpie API.
     *
     * @param string $method HTTP method (GET, POST, PUT, PATCH, DELETE)
     * @param string $path API endpoint path
     * @param array|null $data Request data (body for POST/PUT/PATCH, query params for GET)
     * @param array $options Additional request options
     * @return array Decoded response data
     *
     * @throws MagpieException
     */
    public function request(string $method, string $path, ?array $data = null, array $options = []): array
    {
        $path = ltrim($path, '/');
        
        try {
            $requestOptions = [];

            // Handle request data
            if (in_array(strtoupper($method), ['POST', 'PUT', 'PATCH']) && $data !== null) {
                $requestOptions['json'] = $data;
            } elseif ($data !== null) {
                $requestOptions['query'] = $data;
            }

            // Add idempotency key if provided
            if (isset($options['idempotency_key'])) {
                $requestOptions['headers']['X-Idempotency-Key'] = $options['idempotency_key'];
            }

            // Add expand parameters if provided
            if (isset($options['expand']) && is_array($options['expand'])) {
                $requestOptions['query'] = array_merge($requestOptions['query'] ?? [], [
                    'expand' => $options['expand']
                ]);
            }

            $response = $this->httpClient->request($method, $path, $requestOptions);
            
            return $this->handleResponse($response);

        } catch (RequestException $e) {
            throw $this->createExceptionFromRequestException($e);
        }
    }

    /**
     * Handle successful HTTP response.
     *
     * @param ResponseInterface $response
     * @return array
     * @throws MagpieException
     */
    private function handleResponse(ResponseInterface $response): array
    {
        $body = (string) $response->getBody();
        
        try {
            return json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new MagpieException(
                'Invalid JSON response from API',
                'api_error',
                'invalid_json',
                $response->getStatusCode(),
                $this->getRequestId($response),
                [],
                $response
            );
        }
    }

    /**
     * Create appropriate exception from Guzzle RequestException.
     *
     * @param RequestException $e
     * @return MagpieException
     */
    private function createExceptionFromRequestException(RequestException $e): MagpieException
    {
        $response = $e->getResponse();
        
        if ($response === null) {
            return new NetworkException($e->getMessage(), previous: $e);
        }

        $statusCode = $response->getStatusCode();
        
        // Create appropriate exception based on status code
        $exceptionClass = match (true) {
            $statusCode === 401 => AuthenticationException::class,
            $statusCode === 403 => PermissionException::class,
            $statusCode === 404 => NotFoundException::class,
            $statusCode === 422 => ValidationException::class,
            $statusCode === 429 => RateLimitException::class,
            $statusCode >= 400 && $statusCode < 500 => ValidationException::class,
            default => MagpieException::class
        };
        
        return $exceptionClass::fromResponse($response);
    }

    /**
     * Get request ID from response headers.
     *
     * @param ResponseInterface $response
     * @return string|null
     */
    private function getRequestId(ResponseInterface $response): ?string
    {
        return $response->getHeaderLine('request-id') ?: $response->getHeaderLine('Request-ID') ?: null;
    }

    /**
     * Make a GET request.
     *
     * @param string $path
     * @param array|null $params
     * @param array $options
     * @return array
     * @throws MagpieException
     */
    public function get(string $path, ?array $params = null, array $options = []): array
    {
        return $this->request('GET', $path, $params, $options);
    }

    /**
     * Make a POST request.
     *
     * @param string $path
     * @param array|null $data
     * @param array $options
     * @return array
     * @throws MagpieException
     */
    public function post(string $path, ?array $data = null, array $options = []): array
    {
        return $this->request('POST', $path, $data, $options);
    }

    /**
     * Make a PUT request.
     *
     * @param string $path
     * @param array|null $data
     * @param array $options
     * @return array
     * @throws MagpieException
     */
    public function put(string $path, ?array $data = null, array $options = []): array
    {
        return $this->request('PUT', $path, $data, $options);
    }

    /**
     * Make a PATCH request.
     *
     * @param string $path
     * @param array|null $data
     * @param array $options
     * @return array
     * @throws MagpieException
     */
    public function patch(string $path, ?array $data = null, array $options = []): array
    {
        return $this->request('PATCH', $path, $data, $options);
    }

    /**
     * Make a DELETE request.
     *
     * @param string $path
     * @param array|null $data
     * @param array $options
     * @return array
     * @throws MagpieException
     */
    public function delete(string $path, ?array $data = null, array $options = []): array
    {
        return $this->request('DELETE', $path, $data, $options);
    }

    /**
     * Get the current configuration.
     *
     * @return Config
     */
    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * Test connectivity to the Magpie API.
     *
     * @return bool
     */
    public function ping(): bool
    {
        try {
            $this->get('/ping');
            return true;
        } catch (MagpieException $e) {
            return false;
        }
    }
}
