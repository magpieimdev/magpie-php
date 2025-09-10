<?php

declare(strict_types=1);

namespace Magpie\DTOs\Requests\Checkout;

use Magpie\DTOs\Requests\BaseRequest;
use Magpie\DTOs\ValueObjects\BrandingOptions;
use Magpie\DTOs\ValueObjects\LineItem;
use Magpie\DTOs\ValueObjects\ShippingAddressCollection;
use Magpie\Enums\BillingAddressCollection;
use Magpie\Enums\CheckoutSubmitType;
use Magpie\Enums\SessionMode;

class CreateCheckoutSessionRequest extends BaseRequest
{
    public function __construct(
        public readonly string $cancel_url,
        public readonly string $currency,
        /** @var LineItem[] */
        public readonly array $line_items,
        public readonly SessionMode $mode,
        public readonly array $payment_method_types,
        public readonly string $success_url,
        public readonly ?string $bank_code = null,
        public readonly ?BrandingOptions $branding = null,
        public readonly ?BillingAddressCollection $billing_address_collection = null,
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
        public readonly ?ShippingAddressCollection $shipping_address_collection = null,
        public readonly ?CheckoutSubmitType $submit_type = null
    ) {
    }

    /**
     * Create a CreateCheckoutSessionRequest from an array with automatic conversion.
     */
    public static function fromArray(array $data): static
    {
        return new self(
            cancel_url: $data['cancel_url'],
            currency: $data['currency'],
            line_items: array_map(
                fn ($item) => is_array($item) ? LineItem::fromArray($item) : $item,
                $data['line_items']
            ),
            mode: $data['mode'],
            payment_method_types: $data['payment_method_types'],
            success_url: $data['success_url'],
            bank_code: $data['bank_code'] ?? null,
            branding: isset($data['branding']) && is_array($data['branding'])
                ? BrandingOptions::fromArray($data['branding'])
                : $data['branding'] ?? null,
            billing_address_collection: isset($data['billing_address_collection']) && is_string($data['billing_address_collection'])
                ? BillingAddressCollection::from($data['billing_address_collection'])
                : $data['billing_address_collection'] ?? null,
            client_reference_id: $data['client_reference_id'] ?? null,
            customer: $data['customer'] ?? null,
            customer_email: $data['customer_email'] ?? null,
            customer_name: $data['customer_name'] ?? null,
            customer_name_collection: $data['customer_name_collection'] ?? null,
            customer_phone: $data['customer_phone'] ?? null,
            description: $data['description'] ?? null,
            locale: $data['locale'] ?? null,
            metadata: $data['metadata'] ?? [],
            phone_number_collection: $data['phone_number_collection'] ?? null,
            require_auth: $data['require_auth'] ?? null,
            shipping_address_collection: isset($data['shipping_address_collection']) && is_array($data['shipping_address_collection'])
                ? ShippingAddressCollection::fromArray($data['shipping_address_collection'])
                : $data['shipping_address_collection'] ?? null,
            submit_type: isset($data['submit_type']) && is_string($data['submit_type'])
                ? CheckoutSubmitType::from($data['submit_type'])
                : $data['submit_type'] ?? null,
        );
    }
}
