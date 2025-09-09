<?php

declare(strict_types=1);

namespace Magpie\DTOs\ValueObjects;

/**
 * Link from payment provider response.
 *
 * @internal
 */
class ChargeProviderResponseLink
{
    public function __construct(
        /** The provider response link href */
        public readonly string $href,
        /** The provider response link rel */
        public readonly string $rel
    ) {
    }

    /**
     * Create a ChargeProviderResponseLink from an array.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            href: $data['href'],
            rel: $data['rel']
        );
    }

    /**
     * Convert the ChargeProviderResponseLink to an array.
     */
    public function toArray(): array
    {
        return [
            'href' => $this->href,
            'rel' => $this->rel,
        ];
    }
}
