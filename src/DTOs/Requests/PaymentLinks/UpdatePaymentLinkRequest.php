<?php

declare(strict_types=1);

namespace Magpie\DTOs\Requests\PaymentLinks;

use Magpie\DTOs\Requests\BaseRequest;
use Magpie\DTOs\ValueObjects\BrandingOptions;
use Magpie\DTOs\ValueObjects\PaymentLinkItem;
use Magpie\DTOs\ValueObjects\ShippingAddressCollection;

class UpdatePaymentLinkRequest extends BaseRequest
{
    public function __construct(
        public readonly bool $allow_adjustable_quantity,
        public readonly string $currency,
        public readonly string $internal_name,
        /** @var PaymentLinkItem[] */
        public readonly array $line_items,
        public readonly array $payment_method_types,
        public readonly ?BrandingOptions $branding = null,
        public readonly ?string $description = null,
        public readonly ?string $expiry = null,
        public readonly ?int $maximum_payments = null,
        public readonly array $metadata = [],
        public readonly ?bool $phone_number_collection = null,
        public readonly ?string $redirect_url = null,
        public readonly ?bool $require_auth = null,
        public readonly ?ShippingAddressCollection $shipping_address_collection = null
    ) {}

    /**
     * Create an UpdatePaymentLinkRequest from an array with automatic conversion.
     */
    public static function fromArray(array $data): static
    {
        return new self(
            allow_adjustable_quantity: $data['allow_adjustable_quantity'],
            currency: $data['currency'],
            internal_name: $data['internal_name'],
            line_items: array_map(
                fn($item) => is_array($item) ? PaymentLinkItem::fromArray($item) : $item,
                $data['line_items']
            ),
            payment_method_types: $data['payment_method_types'],
            branding: isset($data['branding']) && is_array($data['branding']) 
                ? BrandingOptions::fromArray($data['branding']) 
                : $data['branding'] ?? null,
            description: $data['description'] ?? null,
            expiry: $data['expiry'] ?? null,
            maximum_payments: $data['maximum_payments'] ?? null,
            metadata: $data['metadata'] ?? [],
            phone_number_collection: $data['phone_number_collection'] ?? null,
            redirect_url: $data['redirect_url'] ?? null,
            require_auth: $data['require_auth'] ?? null,
            shipping_address_collection: isset($data['shipping_address_collection']) && is_array($data['shipping_address_collection'])
                ? ShippingAddressCollection::fromArray($data['shipping_address_collection'])
                : $data['shipping_address_collection'] ?? null,
        );
    }
}