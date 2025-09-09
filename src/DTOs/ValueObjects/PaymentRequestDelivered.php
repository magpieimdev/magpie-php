<?php

declare(strict_types=1);

namespace Magpie\DTOs\ValueObjects;

/**
 * Delivery status for payment request notifications.
 *
 * Tracks whether the payment request was successfully delivered
 * to the customer via different communication channels.
 */
class PaymentRequestDelivered
{
    public function __construct(
        /** Whether the payment request was delivered to the customer via email. */
        public readonly bool $email,
        /** Whether the payment request was delivered to the customer via SMS. */
        public readonly bool $sms
    ) {
    }

    /**
     * Create a PaymentRequestDelivered from an array.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            email: $data['email'] ?? false,
            sms: $data['sms'] ?? false
        );
    }

    /**
     * Convert the PaymentRequestDelivered to an array.
     */
    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'sms' => $this->sms,
        ];
    }
}
