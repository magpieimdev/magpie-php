<?php

declare(strict_types=1);

namespace Magpie\DTOs\ValueObjects;

use Magpie\Enums\RefundStatus;

/**
 * A refund represents the return of funds to a customer.
 * 
 * Refunds are created against charges and return money to the
 * customer's original payment method. They can be full or partial.
 */
class Refund
{
    public function __construct(
        /** The unique identifier of the refund object. */
        public readonly string $id,
        /** The object type literal. */
        public readonly string $object,
        /** The amount refunded in the smallest currency unit (e.g., cents). */
        public readonly int $amount,
        /** 3-letter ISO-code for currency (e.g., PHP). */
        public readonly string $currency,
        /** The reason for the refund. */
        public readonly string $reason,
        /** The status of the refund. */
        public readonly RefundStatus $status,
        /** The creation timestamp of the refund in ISO 8601 format. */
        public readonly string $created_at,
        /** The last update timestamp of the refund in ISO 8601 format. */
        public readonly ?string $updated_at = null
    ) {}

    /**
     * Create a Refund from an array.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            object: $data['object'],
            amount: $data['amount'],
            currency: $data['currency'],
            reason: $data['reason'],
            status: RefundStatus::from($data['status']),
            created_at: $data['created_at'],
            updated_at: $data['updated_at'] ?? null
        );
    }

    /**
     * Convert the Refund to an array.
     */
    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'object' => $this->object,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'reason' => $this->reason,
            'status' => $this->status->value,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ], fn($value) => $value !== null);
    }
}