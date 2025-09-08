<?php

declare(strict_types=1);

namespace Magpie\Contracts;

use Magpie\DTOs\Responses\Organization;

interface OrganizationServiceInterface
{
    public function me(): Organization;
}