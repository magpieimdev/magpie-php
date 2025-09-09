<?php

declare(strict_types=1);

namespace Magpie\Contracts;

interface CheckoutServiceInterface
{
    public function sessions(): CheckoutSessionServiceInterface;
}
