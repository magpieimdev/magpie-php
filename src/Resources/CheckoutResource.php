<?php

declare(strict_types=1);

namespace Magpie\Resources;

use Magpie\Http\Client;

/**
 * Resource class for managing checkout-related operations.
 *
 * The CheckoutResource provides access to checkout sessions and related
 * functionality for creating hosted payment pages and handling customer
 * checkout flows.
 */
class CheckoutResource
{
    /** Resource for managing checkout sessions */
    public readonly CheckoutSessionsResource $sessions;

    /**
     * Create a new CheckoutResource instance.
     *
     * @param Client $client The HTTP client instance for API communication
     */
    public function __construct(Client $client)
    {
        $this->sessions = new CheckoutSessionsResource($client);
    }

    /**
     * Get the checkout sessions resource.
     *
     * @return CheckoutSessionsResource
     */
    public function sessions(): CheckoutSessionsResource
    {
        return $this->sessions;
    }
}
