<?php

declare(strict_types=1);

namespace Magpie\DTOs\ValueObjects;

/**
 * Response data from the payment provider.
 *
 * Contains raw response information from the underlying payment
 * processor for debugging and troubleshooting purposes.
 *
 * @internal
 */
class ChargeProviderResponse
{
    public function __construct(
        /** @var ChargeProviderResponseLink[] The provider response links */
        public readonly array $links,
        /** The provider response code */
        public readonly string $code,
        /** The provider response message */
        public readonly string $message,
        /** The provider response logref */
        public readonly string $logref
    ) {
    }

    /**
     * Create a ChargeProviderResponse from an array.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            links: array_map(
                fn ($link) => is_array($link) ? ChargeProviderResponseLink::fromArray($link) : $link,
                $data['links'] ?? []
            ),
            code: $data['code'],
            message: $data['message'],
            logref: $data['logref']
        );
    }

    /**
     * Convert the ChargeProviderResponse to an array.
     */
    public function toArray(): array
    {
        return [
            'links' => array_map(fn ($link) => $link->toArray(), $this->links),
            'code' => $this->code,
            'message' => $this->message,
            'logref' => $this->logref,
        ];
    }
}
