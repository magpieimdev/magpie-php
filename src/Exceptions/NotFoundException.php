<?php

declare(strict_types=1);

namespace Magpie\Exceptions;

/**
 * Exception thrown when the requested resource is not found (HTTP 404).
 *
 * This exception occurs when trying to access a resource that doesn't exist,
 * such as a customer, charge, or other entity with an invalid or non-existent ID.
 */
class NotFoundException extends MagpieException
{
    /**
     * @var string The error type for this exception
     */
    public ?string $type = 'resource_missing';
}
