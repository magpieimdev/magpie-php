<?php

declare(strict_types=1);

namespace Magpie\Exceptions;

/**
 * Exception thrown when request validation fails.
 *
 * This exception is thrown when the API request contains invalid parameters,
 * missing required fields, or data that doesn't meet the API's validation rules.
 */
class ValidationException extends MagpieException
{
    /**
     * Field validation errors.
     */
    public array $errors = [];

    /**
     * Create a new ValidationException.
     *
     * @param string $message The error message
     * @param array  $errors  Field validation errors
     * @param mixed  ...$args Additional constructor arguments
     */
    public function __construct(string $message = 'Validation failed', array $errors = [], ...$args)
    {
        parent::__construct($message, 'invalid_request_error', ...$args);
        $this->errors = $errors;
    }

    /**
     * Get validation errors for a specific field.
     */
    public function getFieldErrors(string $field): array
    {
        return $this->errors[$field] ?? [];
    }

    /**
     * Check if a field has validation errors.
     */
    public function hasFieldErrors(string $field): bool
    {
        return ! empty($this->errors[$field]);
    }

    /**
     * Create a ValidationException from an HTTP response.
     *
     * @param \Psr\Http\Message\ResponseInterface $response The HTTP response
     */
    public static function fromResponse(\Psr\Http\Message\ResponseInterface $response): self
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
        $rawMessage = $error['message'] ?? $data['message'] ?? "HTTP {$statusCode} Error";

        // Ensure message is always a string, not an array
        if (is_array($rawMessage)) {
            $message = implode(', ', array_filter($rawMessage, 'is_string')) ?: "HTTP {$statusCode} Error";
        } else {
            $message = (string) $rawMessage;
        }

        $type = $error['type'] ?? static::mapStatusToErrorType($statusCode);
        $code = $error['code'] ?? "http_{$statusCode}";
        $details = $error['details'] ?? [];

        // Extract validation errors - they might be in 'errors', 'details', or embedded in message
        $errors = [];
        if (isset($error['errors']) && is_array($error['errors'])) {
            $errors = $error['errors'];
        } elseif (is_array($details)) {
            $errors = $details;
        }

        /* @var static */
        return new static(
            $message,
            $errors,
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
     */
    protected static function mapStatusToErrorType(int $statusCode): string
    {
        return match (true) {
            $statusCode >= 500 => 'api_error',
            429 === $statusCode => 'rate_limit_error',
            401 === $statusCode => 'authentication_error',
            403 === $statusCode => 'permission_error',
            404 === $statusCode => 'not_found_error',
            $statusCode >= 400 => 'invalid_request_error',
            default => 'api_error'
        };
    }
}
