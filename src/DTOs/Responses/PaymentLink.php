<?php

declare(strict_types=1);

namespace Magpie\DTOs\Responses;

use Magpie\DTOs\ValueObjects\BrandingOptions;
use Magpie\DTOs\ValueObjects\PaymentLinkItem;
use Magpie\DTOs\ValueObjects\ShippingAddressCollection;

class PaymentLink extends BaseResponse
{
    public function __construct(
        public readonly string $id,
        public readonly string $object,
        public readonly bool $active,
        public readonly bool $allow_adjustable_quantity,
        public readonly ?BrandingOptions $branding,
        public readonly int $created,
        public readonly string $currency,
        public readonly string $internal_name,
        /** @var PaymentLinkItem[] */
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
        public readonly ?ShippingAddressCollection $shipping_address_collection = null
    ) {}
}