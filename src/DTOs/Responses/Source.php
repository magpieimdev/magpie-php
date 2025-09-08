<?php

declare(strict_types=1);

namespace Magpie\DTOs\Responses;

use Magpie\Enums\SourceType;

class Source extends BaseResponse
{
    public function __construct(
        public readonly string $id,
        public readonly string $object,
        public readonly SourceType $type,
        public readonly array $redirect,
        public readonly bool $vaulted,
        public readonly bool $used,
        public readonly bool $livemode,
        public readonly string $created_at,
        public readonly string $updated_at,
        public readonly array $metadata = [],
        public readonly ?array $card = null,
        public readonly ?array $bank_account = null,
        public readonly ?array $owner = null
    ) {}
}