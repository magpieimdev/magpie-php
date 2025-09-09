<?php

declare(strict_types=1);

namespace Magpie\DTOs\ValueObjects;

/**
 * Merchant information displayed in the checkout session.
 *
 * Contains business details shown to customers during checkout
 * for transparency and support purposes.
 */
class CheckoutSessionMerchant
{
    public function __construct(
        /** The name of the merchant. */
        public readonly string $name,
        /** The support email of the merchant. */
        public readonly ?string $support_email,
        /** The support phone number of the merchant. */
        public readonly ?string $support_phone
    ) {
    }

    /**
     * Create a CheckoutSessionMerchant from an array.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            support_email: $data['support_email'] ?? null,
            support_phone: $data['support_phone'] ?? null
        );
    }

    /**
     * Convert the CheckoutSessionMerchant to an array.
     */
    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'support_email' => $this->support_email,
            'support_phone' => $this->support_phone,
        ], fn ($value) => null !== $value);
    }
}
