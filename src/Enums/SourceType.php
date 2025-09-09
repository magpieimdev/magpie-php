<?php

declare(strict_types=1);

namespace Magpie\Enums;

enum SourceType: string
{
    case CARD = 'card';
    case BPI = 'bpi';
    case QRPH = 'qrph';
    case GCASH = 'gcash';
    case MAYA = 'maya';
    case PAYMAYA = 'paymaya';
}
