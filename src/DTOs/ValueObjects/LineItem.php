<?php

declare(strict_types=1);

namespace Magpie\DTOs\ValueObjects;

/**
 * Represents an individual item or service in a payment or invoice.
 * 
 * Line items are used in checkout sessions, payment links, and invoices
 * to describe the products or services being purchased.
 */
class LineItem
{
    public function __construct(
        /** The name of the line item. */
        public readonly string $name,
        /** The amount of the line item in the smallest currency unit (e.g., cents). */
        public readonly int $amount,
        /** The quantity of the line item being purchased. */
        public readonly int $quantity,
        /** The description of the line item. */
        public readonly ?string $description = null,
        /** The image of the line item. */
        public readonly ?string $image = null
    ) {}

    /**
     * Create a LineItem from an array.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            amount: $data['amount'],
            quantity: $data['quantity'],
            description: $data['description'] ?? null,
            image: $data['image'] ?? null
        );
    }

    /**
     * Convert the LineItem to an array.
     */
    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'amount' => $this->amount,
            'quantity' => $this->quantity,
            'description' => $this->description,
            'image' => $this->image,
        ], fn($value) => $value !== null);
    }
}