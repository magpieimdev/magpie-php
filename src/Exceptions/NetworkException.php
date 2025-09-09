<?php

declare(strict_types=1);

namespace Magpie\Exceptions;

/**
 * Exception thrown when network or connection errors occur.
 *
 * This exception occurs when there are network connectivity issues,
 * timeouts, or other problems communicating with the API servers.
 */
class NetworkException extends MagpieException
{
    /**
     * @var string The error type for this exception
     */
    public ?string $type = 'network_error';

    /**
     * Create a new NetworkException.
     *
     * @param string      $message  The exception message
     * @param ?\Throwable $previous Previous exception
     */
    public function __construct(string $message, ?\Throwable $previous = null)
    {
        parent::__construct($message, $this->type, 'network_error', 0, null, [], null, [], $previous);
    }
}
