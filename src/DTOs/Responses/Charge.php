<?php

declare(strict_types=1);

namespace Magpie\DTOs\Responses;

use Magpie\Enums\ChargeStatus;

class Charge extends BaseResponse
{
    public function __construct(
        public readonly string $id,
        public readonly string $object,
        public readonly int $amount,
        public readonly int $amount_refunded,
        public readonly bool $authorized,
        public readonly bool $captured,
        public readonly string $currency,
        public readonly string $statement_descriptor,
        public readonly string $description,
        public readonly array $source,
        public readonly bool $require_auth,
        public readonly ?array $owner,
        public readonly ?array $action,
        public readonly array $refunds,
        public readonly ChargeStatus $status,
        public readonly bool $livemode,
        public readonly string $created_at,
        public readonly string $updated_at,
        public readonly array $metadata = [],
        public readonly ?array $failure_data = null
    ) {}
}