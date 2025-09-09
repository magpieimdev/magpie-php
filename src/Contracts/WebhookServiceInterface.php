<?php

declare(strict_types=1);

namespace Magpie\Contracts;

interface WebhookServiceInterface
{
    public function constructEvent(
        string $payload,
        string $signature,
        string $secret
    ): mixed;
}
