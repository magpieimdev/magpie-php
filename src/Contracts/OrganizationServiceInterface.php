<?php

declare(strict_types=1);

namespace Magpie\Contracts;

interface OrganizationServiceInterface
{
    public function me(array $options = []): mixed;
}
