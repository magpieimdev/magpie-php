<?php

declare(strict_types=1);

namespace Magpie\DTOs\ValueObjects;

/**
 * Redirect URLs for payment sources that require customer redirection.
 * 
 * Some payment methods require redirecting the customer to complete
 * the payment flow (e.g., online banking, e-wallets).
 */
class SourceRedirect
{
    public function __construct(
        /** The URL to redirect to after the payment is successful. */
        public readonly string $success,
        /** The URL to redirect to after the payment fails. */
        public readonly string $fail,
        /** The URL that will be called repeatedly, until a proper response was received. Works like a payment webhook. */
        public readonly ?string $notify = null
    ) {}

    /**
     * Create a SourceRedirect from an array.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            success: $data['success'],
            fail: $data['fail'],
            notify: $data['notify'] ?? null
        );
    }

    /**
     * Convert the SourceRedirect to an array.
     */
    public function toArray(): array
    {
        return array_filter([
            'success' => $this->success,
            'fail' => $this->fail,
            'notify' => $this->notify,
        ], fn($value) => $value !== null);
    }
}