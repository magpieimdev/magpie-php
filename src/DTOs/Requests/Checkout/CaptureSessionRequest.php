<?php

declare(strict_types=1);

namespace Magpie\DTOs\Requests\Checkout;

use Magpie\DTOs\Requests\BaseRequest;

class CaptureSessionRequest extends BaseRequest
{
    public function __construct(
        public readonly int $amount
    ) {}
}