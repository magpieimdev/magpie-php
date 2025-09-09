<?php

declare(strict_types=1);

namespace Magpie\Enums;

/**
 * Controls how billing address information is collected during checkout.
 * 
 * - `auto`: Collect billing address only if required by payment method
 * - `required`: Always collect billing address
 */
enum BillingAddressCollection: string
{
    /** Collect billing address only if required by payment method */
    case AUTO = 'auto';
    
    /** Always collect billing address */
    case REQUIRED = 'required';
}