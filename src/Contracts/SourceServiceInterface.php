<?php

declare(strict_types=1);

namespace Magpie\Contracts;

use Magpie\DTOs\Responses\Source;

interface SourceServiceInterface
{
    public function retrieve(string $id, array $options = []): mixed;
}