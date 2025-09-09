<?php

declare(strict_types=1);

namespace Magpie\Enums;

enum PaymentStatus: string
{
    case PAID = 'paid';
    case UNPAID = 'unpaid';
    case EXPIRED = 'expired';
    case AUTHORIZED = 'authorized';
    case VOIDED = 'voided';
}
