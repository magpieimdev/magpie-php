<?php

declare(strict_types=1);

namespace Magpie\Enums;

enum SessionMode: string
{
    case PAYMENT = 'payment';
    case SETUP = 'setup';
    case SUBSCRIPTION = 'subscription';
    case SAVE_CARD = 'save_card';
}
