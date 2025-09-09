<?php

declare(strict_types=1);

namespace Magpie\DTOs\ValueObjects;

/**
 * Shipping information including address and recipient details.
 *
 * Extends the base Address interface with shipping-specific fields
 * for delivery and recipient contact information.
 */
class Shipping extends Address
{
    public function __construct(
        /** Customer or recipient name. */
        public readonly string $name,
        /** Customer or recipient phone number. */
        public readonly ?string $phone_number = null,
        /** Customer or recipient email address. */
        public readonly ?string $email = null,
        ?string $line1 = null,
        ?string $line2 = null,
        ?string $city = null,
        ?string $state = null,
        ?string $country = null,
        ?string $zip_code = null
    ) {
        parent::__construct($line1, $line2, $city, $state, $country, $zip_code);
    }

    /**
     * Create a Shipping from an array.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            phone_number: $data['phone_number'] ?? null,
            email: $data['email'] ?? null,
            line1: $data['line1'] ?? null,
            line2: $data['line2'] ?? null,
            city: $data['city'] ?? null,
            state: $data['state'] ?? null,
            country: $data['country'] ?? null,
            zip_code: $data['zip_code'] ?? null
        );
    }

    /**
     * Convert the Shipping to an array.
     */
    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'phone_number' => $this->phone_number,
            'email' => $this->email,
            'line1' => $this->line1,
            'line2' => $this->line2,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'zip_code' => $this->zip_code,
        ], fn ($value) => null !== $value);
    }
}
