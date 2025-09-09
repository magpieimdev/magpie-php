<?php

declare(strict_types=1);

namespace Magpie\DTOs\ValueObjects;

/**
 * Configuration for collecting shipping addresses during checkout.
 * 
 * Specifies which countries are allowed for shipping address collection,
 * enabling geographic restrictions for product fulfillment.
 */
class ShippingAddressCollection
{
    public function __construct(
        /** A list of two-letter ISO country codes. Shipping address will be collected only from these countries. */
        public readonly array $allowed_countries
    ) {}

    /**
     * Create a ShippingAddressCollection from an array.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            allowed_countries: $data['allowed_countries'] ?? []
        );
    }

    /**
     * Convert the ShippingAddressCollection to an array.
     */
    public function toArray(): array
    {
        return [
            'allowed_countries' => $this->allowed_countries,
        ];
    }
}