<?php

declare(strict_types=1);

namespace Magpie\DTOs\Requests\PaymentRequests;

use Magpie\DTOs\Requests\BaseRequest;

class CreatePaymentRequestRequest extends BaseRequest
{
    public function __construct(
        public readonly string $currency,
        public readonly string $customer,
        public readonly array $delivery_methods,
        public readonly array $line_items,
        public readonly array $payment_method_types,
        public readonly ?array $branding = null,
        public readonly ?string $message = null,
        public readonly array $metadata = [],
        public readonly ?bool $require_auth = null
    ) {}
}