<?php

declare(strict_types=1);

namespace Magpie\Exceptions;

/**
 * Exception thrown when authentication fails.
 *
 * This exception is thrown when the provided API key is invalid, expired,
 * or missing the required permissions for the requested operation.
 */
class AuthenticationException extends MagpieException
{
    /**
     * Create a new AuthenticationException.
     *
     * @param string $message The error message
     * @param mixed  ...$args Additional constructor arguments
     */
    public function __construct(string $message = 'Authentication failed', ...$args)
    {
        parent::__construct($message, 'authentication_error', ...$args);
    }
}
