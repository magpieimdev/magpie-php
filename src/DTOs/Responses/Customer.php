<?php

declare(strict_types=1);

namespace Magpie\DTOs\Responses;

class Customer extends BaseResponse
{
    public function __construct(
        public readonly string $id,
        public readonly string $object,
        public readonly string $email,
        public readonly string $description,
        public readonly ?string $mobile_number,
        public readonly bool $livemode,
        public readonly string $created_at,
        public readonly string $updated_at,
        public readonly array $metadata = [],
        public readonly ?string $name = null,
        public readonly array $sources = []
    ) {}
}