<?php

declare(strict_types=1);

namespace Magpie\DTOs\Requests\PaymentRequests;

use Magpie\DTOs\Requests\BaseRequest;
use Magpie\DTOs\ValueObjects\BrandingOptions;
use Magpie\DTOs\ValueObjects\LineItem;

class CreatePaymentRequestRequest extends BaseRequest
{
    public function __construct(
        public readonly string $currency,
        public readonly string $customer,
        public readonly array $delivery_methods,
        /** @var LineItem[] */
        public readonly array $line_items,
        public readonly array $payment_method_types,
        public readonly ?BrandingOptions $branding = null,
        public readonly ?string $message = null,
        public readonly array $metadata = [],
        public readonly ?bool $require_auth = null
    ) {}

    /**
     * Create a CreatePaymentRequestRequest from an array with automatic conversion.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            currency: $data['currency'],
            customer: $data['customer'],
            delivery_methods: $data['delivery_methods'],
            line_items: array_map(
                fn($item) => is_array($item) ? LineItem::fromArray($item) : $item,
                $data['line_items']
            ),
            payment_method_types: $data['payment_method_types'],
            branding: isset($data['branding']) && is_array($data['branding']) 
                ? BrandingOptions::fromArray($data['branding']) 
                : $data['branding'] ?? null,
            message: $data['message'] ?? null,
            metadata: $data['metadata'] ?? [],
            require_auth: $data['require_auth'] ?? null,
        );
    }
}