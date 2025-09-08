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
}

