<?php

declare(strict_types=1);

namespace Magpie\DTOs\ValueObjects;

/**
 * A card object represents a credit or debit card payment source type.
 */
class SourceCard
{
    public function __construct(
        /** The unique identifier of the card object. */
        public readonly string $id,
        /** The object type literal. */
        public readonly string $object,
        /** The name of the card holder. */
        public readonly string $name,
        /** The last 4 digits of the card number. */
        public readonly string $last4,
        /** The card expiration month. */
        public readonly string $exp_month,
        /** The card expiration year. */
        public readonly string $exp_year,
        /** The card brand. */
        public readonly string $brand,
        /** The card country. */
        public readonly string $country,
        /** The card cvc check. */
        public readonly string $cvc_checked,
        /** The card funding type. */
        public readonly string $funding,
        /** The card issuing bank. */
        public readonly string $issuing_bank,
        /** The card billing address line 1. */
        public readonly ?string $address_line1 = null,
        /** The card billing address line 2. */
        public readonly ?string $address_line2 = null,
        /** The card billing address city. */
        public readonly ?string $address_city = null,
        /** The card billing address state. */
        public readonly ?string $address_state = null,
        /** The card billing address country. */
        public readonly ?string $address_country = null,
        /** The card billing address zip code. */
        public readonly ?string $address_zip = null
    ) {}

    /**
     * Create a SourceCard from an array.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            object: $data['object'],
            name: $data['name'],
            last4: $data['last4'],
            exp_month: $data['exp_month'],
            exp_year: $data['exp_year'],
            brand: $data['brand'],
            country: $data['country'],
            cvc_checked: $data['cvc_checked'],
            funding: $data['funding'],
            issuing_bank: $data['issuing_bank'],
            address_line1: $data['address_line1'] ?? null,
            address_line2: $data['address_line2'] ?? null,
            address_city: $data['address_city'] ?? null,
            address_state: $data['address_state'] ?? null,
            address_country: $data['address_country'] ?? null,
            address_zip: $data['address_zip'] ?? null
        );
    }

    /**
     * Convert the SourceCard to an array.
     */
    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'object' => $this->object,
            'name' => $this->name,
            'last4' => $this->last4,
            'exp_month' => $this->exp_month,
            'exp_year' => $this->exp_year,
            'brand' => $this->brand,
            'country' => $this->country,
            'cvc_checked' => $this->cvc_checked,
            'funding' => $this->funding,
            'issuing_bank' => $this->issuing_bank,
            'address_line1' => $this->address_line1,
            'address_line2' => $this->address_line2,
            'address_city' => $this->address_city,
            'address_state' => $this->address_state,
            'address_country' => $this->address_country,
            'address_zip' => $this->address_zip,
        ], fn($value) => $value !== null);
    }
}