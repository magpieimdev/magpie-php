<?php

declare(strict_types=1);

namespace Magpie\Exceptions;

/**
 * Exception thrown when API rate limits are exceeded (HTTP 429).
 *
 * This exception occurs when too many requests have been made to the API
 * in a short period of time. The client should wait before making additional requests.
 */
class RateLimitException extends MagpieException
{
    /**
     * @var string The error type for this exception
     */
    public ?string $type = 'rate_limit_error';
}
