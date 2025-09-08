<?php

declare(strict_types=1);

namespace Magpie\Enums;

enum WebhookEventType: string
{
    case CUSTOMER_CREATED = 'customer.created';
    case CUSTOMER_UPDATED = 'customer.updated';
    case CUSTOMER_DELETED = 'customer.deleted';
    case SOURCE_CREATED = 'source.created';
    case SOURCE_UPDATED = 'source.updated';
    case SOURCE_DELETED = 'source.deleted';
    case CHARGE_CREATED = 'charge.created';
    case CHARGE_UPDATED = 'charge.updated';
    case CHARGE_SUCCEEDED = 'charge.succeeded';
    case CHARGE_FAILED = 'charge.failed';
    case CHARGE_CAPTURED = 'charge.captured';
    case CHARGE_DISPUTED = 'charge.disputed';
    case REFUND_CREATED = 'refund.created';
    case REFUND_UPDATED = 'refund.updated';
    case PAYMENT_REQUEST_CREATED = 'payment_request.created';
    case PAYMENT_REQUEST_UPDATED = 'payment_request.updated';
    case PAYMENT_REQUEST_SUCCEEDED = 'payment_request.succeeded';
    case PAYMENT_REQUEST_FAILED = 'payment_request.failed';
    case CHECKOUT_SESSION_CREATED = 'checkout_session.created';
    case CHECKOUT_SESSION_COMPLETED = 'checkout_session.completed';
    case CHECKOUT_SESSION_EXPIRED = 'checkout_session.expired';
    case PAYMENT_LINK_CREATED = 'payment_link.created';
    case PAYMENT_LINK_UPDATED = 'payment_link.updated';
}