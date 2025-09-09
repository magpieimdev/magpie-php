<?php

declare(strict_types=1);

namespace Magpie\DTOs\ValueObjects;

/**
 * Physical address information.
 * 
 * Used throughout the Magpie API for billing addresses, shipping addresses,
 * and other location-based data. Follows international address standards.
 */
class Address
{
    public function __construct(
        /** Address line 1 (e.g., street, PO Box, or company name). */
        public readonly ?string $line1 = null,
        /** Address line 2 (e.g., apartment, suite, unit, or building). */
        public readonly ?string $line2 = null,
        /** City, district, suburb, town, or village. */
        public readonly ?string $city = null,
        /** State, county, province, or region. */
        public readonly ?string $state = null,
        /** Two-letter country code (ISO 3166-1 alpha-2). */
        public readonly ?string $country = null,
        /** ZIP or postal code. */
        public readonly ?string $zip_code = null
    ) {}

    /**
     * Create an Address from an array.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            line1: $data['line1'] ?? null,
            line2: $data['line2'] ?? null,
            city: $data['city'] ?? null,
            state: $data['state'] ?? null,
            country: $data['country'] ?? null,
            zip_code: $data['zip_code'] ?? null
        );
    }

    /**
     * Convert the Address to an array.
     */
    public function toArray(): array
    {
        return array_filter([
            'line1' => $this->line1,
            'line2' => $this->line2,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'zip_code' => $this->zip_code,
        ], fn($value) => $value !== null);
    }
}