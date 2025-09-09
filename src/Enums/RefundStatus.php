<?php

declare(strict_types=1);

namespace Magpie\Enums;

/**
 * Status of a refund indicating its current processing state.
 * 
 * - `pending`: Refund is being processed
 * - `succeeded`: Refund has been successfully completed
 * - `failed`: Refund processing failed
 */
enum RefundStatus: string
{
    /** Refund is being processed */
    case PENDING = 'pending';
    
    /** Refund has been successfully completed */
    case SUCCEEDED = 'succeeded';
    
    /** Refund processing failed */
    case FAILED = 'failed';
}