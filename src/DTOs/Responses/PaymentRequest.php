<?php

declare(strict_types=1);

namespace Magpie\DTOs\Responses;

class PaymentRequest extends BaseResponse
{
    public function __construct(
        public readonly string $id,
        public readonly string $object,
        public readonly string $account_name,
        public readonly array $branding,
        public readonly int $created,
        public readonly string $currency,
        public readonly string $customer,
        public readonly string $customer_email,
        public readonly string $customer_name,
        public readonly array $delivery_methods,
        public readonly array $delivered,
        public readonly array $line_items,
        public readonly bool $livemode,
        public readonly array $metadata,
        public readonly string $number,
        public readonly bool $paid,
        public readonly array $payment_method_types,
        public readonly string $payment_request_url,
        public readonly bool $require_auth,
        public readonly int $subtotal,
        public readonly int $total,
        public readonly int $updated,
        public readonly bool $voided,
        public readonly ?string $account_support_email = null,
        public readonly ?string $customer_phone = null,
        public readonly ?string $message = null,
        public readonly ?int $paid_at = null,
        public readonly ?array $payment_details = null,
        public readonly ?int $voided_at = null,
        public readonly ?string $void_reason = null
    ) {}
}