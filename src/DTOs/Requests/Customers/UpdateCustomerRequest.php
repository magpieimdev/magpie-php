<?php

declare(strict_types=1);

namespace Magpie\DTOs\Requests\Customers;

use Magpie\DTOs\Requests\BaseRequest;

class UpdateCustomerRequest extends BaseRequest
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $email = null,
        public readonly ?string $mobile_number = null,
        public readonly ?string $description = null,
        public readonly ?array $metadata = null
    ) {
    }
}
