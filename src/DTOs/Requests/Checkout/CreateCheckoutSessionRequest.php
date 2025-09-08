<?php

declare(strict_types=1);

namespace Magpie\DTOs\Requests\Checkout;

use Magpie\DTOs\Requests\BaseRequest;
use Magpie\Enums\SessionMode;

class CreateCheckoutSessionRequest extends BaseRequest
{
    public function __construct(
        public readonly string $cancel_url,
        public readonly string $currency,
        public readonly array $line_items,
        public readonly SessionMode $mode,
        public readonly array $payment_method_types,
        public readonly string $success_url,
        public readonly ?string $bank_code = null,
        public readonly ?array $branding = null,
        public readonly ?string $billing_address_collection = null,
        public readonly ?string $client_reference_id = null,
        public readonly ?string $customer = null,
        public readonly ?string $customer_email = null,
        public readonly ?string $customer_name = null,
        public readonly ?bool $customer_name_collection = null,
        public readonly ?string $customer_phone = null,
        public readonly ?string $description = null,
        public readonly ?string $locale = null,
        public readonly array $metadata = [],
        public readonly ?bool $phone_number_collection = null,
        public readonly ?bool $require_auth = null,
        public readonly ?array $shipping_address_collection = null,
        public readonly ?string $submit_type = null
    ) {}
}