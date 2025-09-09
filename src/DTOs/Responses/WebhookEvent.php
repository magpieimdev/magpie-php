<?php

declare(strict_types=1);

namespace Magpie\DTOs\Responses;

use Magpie\Enums\WebhookEventType;

class WebhookEvent extends BaseResponse
{
    public function __construct(
        public readonly string $id,
        public readonly WebhookEventType $type,
        public readonly array $data,
        public readonly int $created,
        public readonly bool $livemode,
        public readonly ?string $api_version = null,
        public readonly ?int $pending_webhooks = null,
        public readonly ?array $request = null
    ) {
    }
}
