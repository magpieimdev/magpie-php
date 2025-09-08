<?php

declare(strict_types=1);

namespace Magpie\DTOs\Responses;

class PaymentLink extends BaseResponse
{
    public function __construct(
        public readonly string $id,
        public readonly string $object,
        public readonly bool $active,
        public readonly bool $allow_adjustable_quantity,
        public readonly array $branding,
        public readonly int $created,
        public readonly string $currency,
        public readonly string $internal_name,
        public readonly array $line_items,
        public readonly bool $livemode,
        public readonly array $metadata,
        public readonly array $payment_method_types,
        public readonly bool $require_auth,
        public readonly int $updated,
        public readonly string $url,
        public readonly ?string $description = null,
        public readonly ?string $expiry = null,
        public readonly ?int $maximum_payments = null,
        public readonly ?bool $phone_number_collection = null,
        public readonly ?string $redirect_url = null,
        public readonly ?array $shipping_address_collection = null
    ) {}
}