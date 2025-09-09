<?php

declare(strict_types=1);

namespace Magpie\Contracts;

interface SourceServiceInterface
{
    public function retrieve(string $id, array $options = []): mixed;
}
