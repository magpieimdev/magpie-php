<?php

declare(strict_types=1);

namespace Magpie\Exceptions;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * Base exception class for all Magpie API errors.
 *
 * This class provides the foundation for all exceptions thrown by the Magpie SDK,
 * including HTTP errors, API errors, and client-side errors. It includes rich
 * error information for debugging and handling different error scenarios.
 */
class MagpieException extends Exception
{
    /**
     * The error type as returned by the API.
     */
    public ?string $type = null;

    /**
     * The error code as returned by the API.
     */
    public ?string $errorCode = null;

    /**
     * The HTTP status code of the response that caused this exception.
     */
    public ?int $statusCode = null;

    /**
     * The unique request ID for this API call, useful for support.
     */
    public ?string $requestId = null;

    /**
     * Additional error details provided by the API.
     */
    public array $details = [];

    /**
     * The raw HTTP response that caused this exception.
     */
    public ?ResponseInterface $response = null;

    /**
     * HTTP response headers from the failed request.
     */
    public array $headers = [];

    /**
     * Create a new MagpieException instance.
     *
     * @param string $message The error message
     * @param string|null $type The error type from the API
     * @param string|null $code The error code from the API
     * @param int|null $statusCode HTTP status code
     * @param string|null $requestId The request ID for debugging
     * @param array $details Additional error details
     * @param ResponseInterface|null $response The raw HTTP response
     * @param array $headers Response headers
     * @param Throwable|null $previous Previous exception for chaining
     */
    public function __construct(
        string $message = '',
        ?string $type = null,
        ?string $code = null,
        ?int $statusCode = null,
        ?string $requestId = null,
        array $details = [],
        ?ResponseInterface $response = null,
        array $headers = [],
        ?Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
        
        $this->type = $type;
        $this->errorCode = $code;
        $this->statusCode = $statusCode;
        $this->requestId = $requestId;
        $this->details = $details;
        $this->response = $response;
        $this->headers = $headers;
    }

    /**
     * Create a MagpieException from an HTTP response.
     *
     * @param ResponseInterface $response The HTTP response
     * @return static
     */
    public static function fromResponse(ResponseInterface $response): static
    {
        $statusCode = $response->getStatusCode();
        $body = $response->getBody()->getContents();
        $headers = [];
        
        // Convert headers to array
        foreach ($response->getHeaders() as $name => $values) {
            $headers[$name] = implode(', ', $values);
        }
        
        $requestId = $headers['request-id'] ?? $headers['Request-ID'] ?? null;
        
        try {
            $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            $data = ['error' => ['message' => 'Invalid JSON response']];
        }
        
        $error = $data['error'] ?? $data;
        $message = $error['message'] ?? $data['message'] ?? "HTTP {$statusCode} Error";
        $type = $error['type'] ?? static::mapStatusToErrorType($statusCode);
        $code = $error['code'] ?? "http_{$statusCode}";
        $details = $error['details'] ?? [];
        
        return new static(
            $message,
            $type,
            $code,
            $statusCode,
            $requestId,
            $details,
            $response,
            $headers
        );
    }

    /**
     * Map HTTP status codes to error types.
     *
     * @param int $statusCode
     * @return string
     */
    protected static function mapStatusToErrorType(int $statusCode): string
    {
        return match (true) {
            $statusCode >= 500 => 'api_error',
            $statusCode === 429 => 'rate_limit_error',
            $statusCode === 401 => 'authentication_error',
            $statusCode === 403 => 'permission_error',
            $statusCode === 404 => 'not_found_error',
            $statusCode >= 400 => 'invalid_request_error',
            default => 'api_error'
        };
    }

    /**
     * Get a user-friendly description of the error.
     *
     * @return string
     */
    public function getUserMessage(): string
    {
        return match ($this->type) {
            'authentication_error' => 'Authentication failed. Please check your API key.',
            'permission_error' => 'You do not have permission to perform this action.',
            'rate_limit_error' => 'Too many requests. Please try again later.',
            'not_found_error' => 'The requested resource was not found.',
            'invalid_request_error' => 'The request was invalid. Please check your parameters.',
            'network_error' => 'Network error occurred. Please check your connection.',
            default => 'An error occurred while processing your request.'
        };
    }

    /**
     * Get all error information as an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'message' => $this->getMessage(),
            'type' => $this->type,
            'code' => $this->errorCode,
            'status_code' => $this->statusCode,
            'request_id' => $this->requestId,
            'details' => $this->details,
            'headers' => $this->headers,
        ];
    }

    /**
     * Convert the exception to a JSON string.
     *
     * @return string
     */
    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_PRETTY_PRINT);
    }

    /**
     * Check if this is a specific type of error.
     *
     * @param string $type
     * @return bool
     */
    public function isType(string $type): bool
    {
        return $this->type === $type;
    }

    /**
     * Check if this error is retryable.
     *
     * @return bool
     */
    public function isRetryable(): bool
    {
        return in_array($this->type, ['rate_limit_error', 'network_error'])
            || ($this->statusCode && $this->statusCode >= 500);
    }
}
