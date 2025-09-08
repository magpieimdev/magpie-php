<?php

declare(strict_types=1);

namespace Magpie\DTOs\Requests\Customers;

use Magpie\DTOs\Requests\BaseRequest;

class CreateCustomerRequest extends BaseRequest
{
    public function __construct(
        public readonly string $email,
        public readonly string $description,
        public readonly ?string $name = null,
        public readonly ?string $mobile_number = null,
        public readonly array $metadata = []
    ) {}
}