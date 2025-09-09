<?php

declare(strict_types=1);

namespace Magpie\Enums;

/**
 * Type of transaction for customizing the submit button text.
 * 
 * - `pay`: Generic "Pay" button
 * - `book`: "Book" for reservations or appointments
 * - `donate`: "Donate" for charitable contributions
 * - `send`: "Send" for money transfers or gifts
 */
enum CheckoutSubmitType: string
{
    /** Generic "Pay" button */
    case PAY = 'pay';
    
    /** "Book" for reservations or appointments */
    case BOOK = 'book';
    
    /** "Donate" for charitable contributions */
    case DONATE = 'donate';
    
    /** "Send" for money transfers or gifts */
    case SEND = 'send';
}