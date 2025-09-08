<?php

declare(strict_types=1);

namespace Magpie\DTOs\Responses;

class Organization extends BaseResponse
{
    public function __construct(
        public readonly string $object,
        public readonly string $id,
        public readonly string $title,
        public readonly string $account_name,
        public readonly string $statement_descriptor,
        public readonly string $pk_test_key,
        public readonly string $sk_test_key,
        public readonly string $pk_live_key,
        public readonly string $sk_live_key,
        public readonly array $branding,
        public readonly string $status,
        public readonly string $created_at,
        public readonly string $updated_at,
        public readonly array $payment_method_settings,
        public readonly array $rates,
        public readonly array $payout_settings,
        public readonly array $metadata = [],
        public readonly ?string $business_address = null
    ) {}
}