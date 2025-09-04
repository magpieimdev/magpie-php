<?php

declare(strict_types=1);

namespace Magpie\Resources;

use Magpie\Http\Client;

/**
 * Resource class for managing payment sources (cards, bank accounts, etc.).
 */
class SourcesResource extends BaseResource
{
    public function __construct(Client $client)
    {
        parent::__construct($client, '/sources');
    }

    public function create(array $params, array $options = []): array
    {
        return parent::create($params, $options);
    }

    public function retrieve(string $id, array $options = []): array
    {
        return parent::retrieve($id, $options);
    }
}

/**
 * Resource class for managing checkout sessions.
 */
class CheckoutResource extends BaseResource
{
    public function __construct(Client $client)
    {
        parent::__construct($client, '/checkout');
    }

    public function sessions(): CheckoutSessionsResource
    {
        return new CheckoutSessionsResource($this->client);
    }
}

/**
 * Resource class for checkout sessions.
 */
class CheckoutSessionsResource extends BaseResource
{
    public function __construct(Client $client)
    {
        parent::__construct($client, '/checkout/sessions');
    }

    public function create(array $params, array $options = []): array
    {
        return parent::create($params, $options);
    }

    public function retrieve(string $id, array $options = []): array
    {
        return parent::retrieve($id, $options);
    }
}

/**
 * Resource class for managing payment requests.
 */
class PaymentRequestsResource extends BaseResource
{
    public function __construct(Client $client)
    {
        parent::__construct($client, '/payment-requests');
    }

    public function create(array $params, array $options = []): array
    {
        return parent::create($params, $options);
    }

    public function retrieve(string $id, array $options = []): array
    {
        return parent::retrieve($id, $options);
    }
}

/**
 * Resource class for managing payment links.
 */
class PaymentLinksResource extends BaseResource
{
    public function __construct(Client $client)
    {
        parent::__construct($client, '/payment-links');
    }

    public function create(array $params, array $options = []): array
    {
        return parent::create($params, $options);
    }

    public function retrieve(string $id, array $options = []): array
    {
        return parent::retrieve($id, $options);
    }
}

/**
 * Resource class for managing webhooks.
 */
class WebhooksResource extends BaseResource
{
    public function __construct(Client $client)
    {
        parent::__construct($client, '/webhooks');
    }

    /**
     * Construct a webhook event from the request body and signature.
     *
     * @param string $payload The request body
     * @param string $signature The webhook signature
     * @param string $secret The webhook secret
     * @return array The verified webhook event
     */
    public function constructEvent(string $payload, string $signature, string $secret): array
    {
        // TODO: Implement webhook signature verification
        return json_decode($payload, true);
    }
}
