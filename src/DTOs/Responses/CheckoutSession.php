<?php

declare(strict_types=1);

namespace Magpie\DTOs\Responses;

use Magpie\DTOs\ValueObjects\BrandingOptions;
use Magpie\DTOs\ValueObjects\CheckoutSessionAddress;
use Magpie\DTOs\ValueObjects\CheckoutSessionMerchant;
use Magpie\DTOs\ValueObjects\LineItem;
use Magpie\DTOs\ValueObjects\ShippingAddressCollection;
use Magpie\Enums\BillingAddressCollection;
use Magpie\Enums\CheckoutSubmitType;
use Magpie\Enums\PaymentStatus;
use Magpie\Enums\SessionMode;

class CheckoutSession extends BaseResponse
{
    public function __construct(
        public readonly string $id,
        public readonly string $object,
        public readonly int $amount_subtotal,
        public readonly int $amount_total,
        public readonly ?BrandingOptions $branding,
        public readonly BillingAddressCollection $billing_address_collection,
        public readonly string $cancel_url,
        public readonly string $created_at,
        public readonly string $expires_at,
        public readonly string $currency,
        public readonly bool $customer_name_collection,
        public readonly string $last_updated,
        /** @var LineItem[] */
        public readonly array $line_items,
        public readonly bool $livemode,
        public readonly string $locale,
        public readonly CheckoutSessionMerchant $merchant,
        public readonly array $metadata,
        public readonly SessionMode $mode,
        public readonly array $payment_method_types,
        public readonly PaymentStatus $payment_status,
        public readonly string $payment_url,
        public readonly bool $phone_number_collection,
        public readonly bool $require_auth,
        public readonly CheckoutSubmitType $submit_type,
        public readonly string $success_url,
        public readonly ?string $bank_code = null,
        public readonly ?CheckoutSessionAddress $billing = null,
        public readonly ?string $client_reference_id = null,
        public readonly ?string $customer = null,
        public readonly ?string $customer_email = null,
        public readonly ?string $customer_name = null,
        public readonly ?string $customer_phone = null,
        public readonly ?string $description = null,
        public readonly ?Charge $payment_details = null,
        public readonly ?CheckoutSessionAddress $shipping = null,
        public readonly ?ShippingAddressCollection $shipping_address_collection = null
    ) {
    }
}
