<?php

declare(strict_types=1);

namespace Magpie\Resources;

use Magpie\Exceptions\MagpieException;
use Magpie\Http\Client;

/**
 * Resource class for managing customers.
 *
 * The CustomersResource provides methods to create, retrieve, update, and manage customer
 * records in the Magpie payment system. Customers can have attached payment sources
 * and can be used for recurring billing and payment history tracking.
 */
class CustomersResource extends BaseResource
{
    public function __construct(Client $client)
    {
        parent::__construct($client, 'customers');
    }

    /**
     * Create a new customer.
     *
     * @param array $params  Customer creation parameters
     * @param array $options Additional request options
     *
     * @return array Created customer data
     *
     * @throws MagpieException
     *
     * @example
     * ```php
     * $customer = $magpie->customers->create([
     *     'name' => 'John Doe',
     *     'email' => 'john@example.com',
     *     'phone' => '+639151234567',
     *     'description' => 'Premium customer'
     * ]);
     * ```
     */
    public function create(array $params, array $options = []): array
    {
        return parent::create($params, $options);
    }

    /**
     * Retrieve a customer by ID.
     *
     * @param string $id      Customer ID
     * @param array  $options Additional request options
     *
     * @return array Customer data
     *
     * @throws MagpieException
     */
    public function retrieve(string $id, array $options = []): array
    {
        return parent::retrieve($id, $options);
    }

    /**
     * Update a customer.
     *
     * @param string $id      Customer ID
     * @param array  $params  Update parameters
     * @param array  $options Additional request options
     *
     * @return array Updated customer data
     *
     * @throws MagpieException
     */
    public function update(string $id, array $params, array $options = []): array
    {
        return parent::update($id, $params, $options);
    }

    /**
     * Retrieve a customer by their email address.
     *
     * @param string $email   The email address of the customer
     * @param array  $options Additional request options
     *
     * @return array Customer data
     *
     * @throws MagpieException
     *
     * @example
     * ```php
     * $customer = $magpie->customers->retrieveByEmail('john@example.com');
     * ```
     */
    public function retrieveByEmail(string $email, array $options = []): array
    {
        return $this->customAction('GET', $this->buildPath().'/by_email/'.$email, null, $options);
    }

    /**
     * Attach a payment source to a customer.
     *
     * @param string $id      The unique identifier of the customer
     * @param string $source  The ID of the payment source to attach
     * @param array  $options Additional request options
     *
     * @return array Updated customer data
     *
     * @throws MagpieException
     *
     * @example
     * ```php
     * $updatedCustomer = $magpie->customers->attachSource(
     *     'cus_123',
     *     'src_456'
     * );
     * ```
     */
    public function attachSource(string $id, string $source, array $options = []): array
    {
        return $this->customResourceAction('POST', $id, 'sources', ['source' => $source], $options);
    }

    /**
     * Detach a payment source from a customer.
     *
     * @param string $id      The unique identifier of the customer
     * @param string $source  The ID of the payment source to detach
     * @param array  $options Additional request options
     *
     * @return array Updated customer data
     *
     * @throws MagpieException
     *
     * @example
     * ```php
     * $updatedCustomer = $magpie->customers->detachSource(
     *     'cus_123',
     *     'src_456'
     * );
     * ```
     */
    public function detachSource(string $id, string $source, array $options = []): array
    {
        return $this->customAction('DELETE', $this->buildPath($id, "sources/{$source}"), null, $options);
    }
}
