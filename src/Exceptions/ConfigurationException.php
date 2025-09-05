<?php

declare(strict_types=1);

namespace Magpie\Exceptions;

/**
 * Exception thrown when there are configuration errors.
 *
 * This includes invalid API keys, missing configuration parameters,
 * or other setup-related issues that prevent the SDK from functioning properly.
 */
class ConfigurationException extends MagpieException
{
    /**
     * Create a new ConfigurationException instance.
     *
     * @param string          $message    The error message
     * @param string          $type       The error type (default: 'invalid_request_error')
     * @param string|null     $code       The error code
     * @param int             $statusCode The HTTP status code (default: 400)
     * @param array|null      $headers    Additional response headers
     * @param \Throwable|null $previous   Previous exception for chaining
     */
    public function __construct(
        string $message,
        string $type = 'invalid_request_error',
        ?string $code = null,
        int $statusCode = 400,
        ?array $headers = null,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $type, $code, $statusCode, null, [], null, $headers ?? [], $previous);
    }
}
