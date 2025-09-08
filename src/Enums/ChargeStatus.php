<?php

declare(strict_types=1);

namespace Magpie\Enums;

enum ChargeStatus: string
{
    case PENDING = 'pending';
    case SUCCEEDED = 'succeeded';
    case FAILED = 'failed';
}