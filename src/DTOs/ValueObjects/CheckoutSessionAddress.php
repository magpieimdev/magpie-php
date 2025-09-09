<?php

declare(strict_types=1);

namespace Magpie\DTOs\ValueObjects;

/**
 * Address information collected during checkout.
 *
 * Includes detailed address fields specific to Philippine
 * address format including barangay information.
 */
class CheckoutSessionAddress
{
    public function __construct(
        /** The customer's full name or business name. */
        public readonly string $name,
        /** The first line of the address. */
        public readonly string $line1,
        /** The second line of the address. */
        public readonly ?string $line2,
        /** The barangay of the address. */
        public readonly string $barangay,
        /** The city of the address. */
        public readonly string $city,
        /** The state of the address. */
        public readonly string $state,
        /** The zip code of the address. */
        public readonly string $zip_code,
        /** The country of the address. */
        public readonly string $country
    ) {
    }

    /**
     * Create a CheckoutSessionAddress from an array.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            line1: $data['line1'],
            line2: $data['line2'] ?? null,
            barangay: $data['barangay'],
            city: $data['city'],
            state: $data['state'],
            zip_code: $data['zip_code'],
            country: $data['country']
        );
    }

    /**
     * Convert the CheckoutSessionAddress to an array.
     */
    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'line1' => $this->line1,
            'line2' => $this->line2,
            'barangay' => $this->barangay,
            'city' => $this->city,
            'state' => $this->state,
            'zip_code' => $this->zip_code,
            'country' => $this->country,
        ], fn ($value) => null !== $value);
    }
}
