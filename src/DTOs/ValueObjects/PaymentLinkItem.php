<?php

declare(strict_types=1);

namespace Magpie\DTOs\ValueObjects;

/**
 * A line item for a payment link with inventory tracking.
 * 
 * Extends the base LineItem with additional fields specific
 * to payment links, including stock management.
 */
class PaymentLinkItem extends LineItem
{
    public function __construct(
        int $amount,
        int $quantity,
        ?string $description = null,
        ?string $image = null,
        /** The total number of stocks remaining. */
        public readonly int $remaining = 0
    ) {
        parent::__construct($amount, $quantity, $description, $image);
    }

    /**
     * Create a PaymentLinkItem from an array.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            amount: $data['amount'],
            quantity: $data['quantity'],
            description: $data['description'] ?? null,
            image: $data['image'] ?? null,
            remaining: $data['remaining'] ?? 0
        );
    }

    /**
     * Convert the PaymentLinkItem to an array.
     */
    public function toArray(): array
    {
        return array_filter([
            'amount' => $this->amount,
            'quantity' => $this->quantity,
            'description' => $this->description,
            'image' => $this->image,
            'remaining' => $this->remaining,
        ], fn($value) => $value !== null);
    }
}