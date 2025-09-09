<?php

declare(strict_types=1);

namespace Magpie\DTOs\Requests\Charges;

use Magpie\DTOs\Requests\BaseRequest;

class RefundChargeRequest extends BaseRequest
{
    public function __construct(
        public readonly int $amount,
        public readonly string $reason
    ) {
    }
}
