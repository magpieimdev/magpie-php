<?php

declare(strict_types=1);

namespace Magpie\DTOs\ValueObjects;

/**
 * Billing information including address and contact details.
 * 
 * Extends the base Address interface with billing-specific fields
 * like name, phone, and email for payment processing.
 */
class Billing extends Address
{
    public function __construct(
        /** The customer's full name or business name. */
        public readonly string $name,
        /** The customer's phone number. */
        public readonly ?string $phone_number = null,
        /** The customer's email address. */
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
     * Create a Billing from an array.
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
     * Convert the Billing to an array.
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
        ], fn($value) => $value !== null);
    }
}