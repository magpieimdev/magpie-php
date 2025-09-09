<?php

declare(strict_types=1);

namespace Magpie\DTOs\ValueObjects;

/**
 * Information about the owner of a payment source.
 * 
 * Contains contact and address information for the person or
 * entity that owns the payment method.
 */
class SourceOwner
{
    public function __construct(
        /** The name of the owner. */
        public readonly string $name,
        /** The country of the owner. */
        public readonly ?string $address_country = null,
        /** The billing address of the owner. */
        public readonly ?Billing $billing = null,
        /** The shipping address of the owner. */
        public readonly ?Shipping $shipping = null
    ) {}

    /**
     * Create a SourceOwner from an array.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            address_country: $data['address_country'] ?? null,
            billing: isset($data['billing']) && is_array($data['billing']) 
                ? Billing::fromArray($data['billing']) 
                : null,
            shipping: isset($data['shipping']) && is_array($data['shipping']) 
                ? Shipping::fromArray($data['shipping']) 
                : null
        );
    }

    /**
     * Convert the SourceOwner to an array.
     */
    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'address_country' => $this->address_country,
            'billing' => $this->billing?->toArray(),
            'shipping' => $this->shipping?->toArray(),
        ], fn($value) => $value !== null);
    }
}