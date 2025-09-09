<?php

declare(strict_types=1);

namespace Magpie\DTOs\Requests\PaymentRequests;

use Magpie\DTOs\Requests\BaseRequest;

class VoidPaymentRequestRequest extends BaseRequest
{
    public function __construct(
        public readonly string $reason
    ) {
    }
}
