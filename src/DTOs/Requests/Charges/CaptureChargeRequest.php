<?php

declare(strict_types=1);

namespace Magpie\DTOs\Requests\Charges;

use Magpie\DTOs\Requests\BaseRequest;

class CaptureChargeRequest extends BaseRequest
{
    public function __construct(
        public readonly int $amount
    ) {}
}