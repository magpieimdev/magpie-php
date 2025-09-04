<?php

declare(strict_types=1);

namespace Magpie\Resources;

use Magpie\Http\Client;

/**
 * Resource class for managing customers.
 *
 * The CustomersResource provides methods to create, retrieve, update, and delete
 * customers in the Magpie payment system.
 */
class CustomersResource extends BaseResource
{
    public function __construct(Client $client)
    {
        parent::__construct($client, '/customers');
    }

    /**
     * Create a new customer.
     *
     * @param array $params Customer creation parameters
     * @param array $options Additional request options
     * @return array Created customer data
     */
    public function create(array $params, array $options = []): array
    {
        return parent::create($params, $options);
    }

    /**
     * Retrieve a customer by ID.
     *
     * @param string $id Customer ID
     * @param array $options Additional request options
     * @return array Customer data
     */
    public function retrieve(string $id, array $options = []): array
    {
        return parent::retrieve($id, $options);
    }

    /**
     * Update a customer.
     *
     * @param string $id Customer ID
     * @param array $params Update parameters
     * @param array $options Additional request options
     * @return array Updated customer data
     */
    public function update(string $id, array $params, array $options = []): array
    {
        return parent::update($id, $params, $options);
    }
}
