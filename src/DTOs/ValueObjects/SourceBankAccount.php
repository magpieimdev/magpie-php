<?php

declare(strict_types=1);

namespace Magpie\DTOs\ValueObjects;

/**
 * A bank account object represents a bank account payment source type.
 */
class SourceBankAccount
{
    public function __construct(
        /** The reference ID of the bank account. */
        public readonly string $reference_id,
        /** The type of the bank account. */
        public readonly string $bank_type,
        /** The bank code of the bank account. */
        public readonly string $bank_code,
        /** The name of the account owner. */
        public readonly string $account_name,
        /** The account number. */
        public readonly string $account_number,
        /** The type of the account. */
        public readonly string $account_type,
        /** The expiration date of the bank transaction session. */
        public readonly string $expires_at,
        /** Set of key-value pairs attached to the bank account object. */
        public readonly array $metadata
    ) {}

    /**
     * Create a SourceBankAccount from an array.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            reference_id: $data['reference_id'],
            bank_type: $data['bank_type'],
            bank_code: $data['bank_code'],
            account_name: $data['account_name'],
            account_number: $data['account_number'],
            account_type: $data['account_type'],
            expires_at: $data['expires_at'],
            metadata: $data['metadata'] ?? []
        );
    }

    /**
     * Convert the SourceBankAccount to an array.
     */
    public function toArray(): array
    {
        return [
            'reference_id' => $this->reference_id,
            'bank_type' => $this->bank_type,
            'bank_code' => $this->bank_code,
            'account_name' => $this->account_name,
            'account_number' => $this->account_number,
            'account_type' => $this->account_type,
            'expires_at' => $this->expires_at,
            'metadata' => $this->metadata,
        ];
    }
}