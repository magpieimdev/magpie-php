<?php

declare(strict_types=1);

namespace Magpie\DTOs\Requests\PaymentLinks;

use Magpie\DTOs\Requests\BaseRequest;

class CreatePaymentLinkRequest extends BaseRequest
{
    public function __construct(
        public readonly bool $allow_adjustable_quantity,
        public readonly string $currency,
        public readonly string $internal_name,
        public readonly array $line_items,
        public readonly array $payment_method_types,
        public readonly ?array $branding = null,
        public readonly ?string $description = null,
        public readonly ?string $expiry = null,
        public readonly ?int $maximum_payments = null,
        public readonly array $metadata = [],
        public readonly ?bool $phone_number_collection = null,
        public readonly ?string $redirect_url = null,
        public readonly ?bool $require_auth = null,
        public readonly ?array $shipping_address_collection = null
    ) {}
}