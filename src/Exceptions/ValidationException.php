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
     * @param array $errors Field validation errors
     * @param mixed ...$args Additional constructor arguments
     */
    public function __construct(string $message = 'Validation failed', array $errors = [], ...$args)
    {
        parent::__construct($message, 'invalid_request_error', ...$args);
        $this->errors = $errors;
    }

    /**
     * Get validation errors for a specific field.
     *
     * @param string $field
     * @return array
     */
    public function getFieldErrors(string $field): array
    {
        return $this->errors[$field] ?? [];
    }

    /**
     * Check if a field has validation errors.
     *
     * @param string $field
     * @return bool
     */
    public function hasFieldErrors(string $field): bool
    {
        return !empty($this->errors[$field]);
    }
}

/**
 * Exception thrown when rate limiting is enforced.
 */
class RateLimitException extends MagpieException
{
    public function __construct(string $message = 'Rate limit exceeded', ...$args)
    {
        parent::__construct($message, 'rate_limit_error', ...$args);
    }
}

/**
 * Exception thrown when a network error occurs.
 */
class NetworkException extends MagpieException
{
    public function __construct(string $message = 'Network error', ...$args)
    {
        parent::__construct($message, 'network_error', ...$args);
    }
}

/**
 * Exception thrown when a requested resource is not found.
 */
class NotFoundException extends MagpieException
{
    public function __construct(string $message = 'Resource not found', ...$args)
    {
        parent::__construct($message, 'not_found_error', ...$args);
    }
}

/**
 * Exception thrown when permission is denied.
 */
class PermissionException extends MagpieException
{
    public function __construct(string $message = 'Permission denied', ...$args)
    {
        parent::__construct($message, 'permission_error', ...$args);
    }
}

/**
 * Exception thrown when API configuration is invalid.
 */
class ConfigurationException extends MagpieException
{
    public function __construct(string $message = 'Configuration error', ...$args)
    {
        parent::__construct($message, 'configuration_error', ...$args);
    }
}
