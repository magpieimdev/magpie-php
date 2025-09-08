<?php

declare(strict_types=1);

namespace Magpie\Exceptions;

/**
 * Exception thrown when the API request is forbidden (HTTP 403).
 *
 * This exception occurs when the API key does not have the necessary
 * permissions to perform the requested operation, or when trying to
 * access resources that are not allowed for the current account.
 */
class PermissionException extends MagpieException
{
    /**
     * @var string The error type for this exception
     */
    public ?string $type = 'permission_error';
}
