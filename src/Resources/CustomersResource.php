<?php

declare(strict_types=1);

namespace Magpie\Resources;

use Magpie\Exceptions\MagpieException;
use Magpie\Http\Client;
use Magpie\DTOs\Requests\Customers\CreateCustomerRequest;
use Magpie\DTOs\Requests\Customers\UpdateCustomerRequest;
use Magpie\DTOs\Responses\Customer;
use Magpie\Contracts\CustomerServiceInterface;

/**
 * Resource class for managing customers.
 *
 * The CustomersResource provides methods to create, retrieve, update, and manage customer
 * records in the Magpie payment system. Customers can have attached payment sources
 * and can be used for recurring billing and payment history tracking.
 */
class CustomersResource extends BaseResource implements CustomerServiceInterface
{
    public function __construct(Client $client)
    {
        parent::__construct($client, 'customers');
    }

    /**
     * Create a new customer.
     *
     * @param CreateCustomerRequest|array $request  Customer creation parameters or DTO
     * @param array                       $options  Additional request options
     *
     * @return Customer Created customer data
     *
     * @throws MagpieException
     *
     * @example
     * ```php
     * // Using array (backward compatible)
     * $customer = $magpie->customers->create([
     *     'name' => 'John Doe',
     *     'email' => 'john@example.com',
     *     'mobile_number' => '+639151234567',
     *     'description' => 'Premium customer'
     * ]);
     *
     * // Using DTO (type-safe)
     * $request = new CreateCustomerRequest(
     *     email: 'john@example.com',
     *     description: 'Premium customer',
     *     name: 'John Doe',
     *     mobile_number: '+639151234567'
     * );
     * $customer = $magpie->customers->create($request);
     * ```
     */
    public function create(CreateCustomerRequest|array $request, array $options = []): mixed
    {
        if (is_array($request)) {
            $transformedRequest = $this->transformCustomerPayload($request);
            $customer = parent::create($transformedRequest, $options);
            $transformedCustomer = $this->transformCustomerResponse($customer);
            return $this->createFromArray($transformedCustomer);
        }
        
        $transformedRequest = $this->transformCustomerPayload($request->toArray());
        $customer = parent::create($transformedRequest, $options);
        $transformedCustomer = $this->transformCustomerResponse($customer);
        return $this->createFromArray($transformedCustomer);
    }

    /**
     * Retrieve a customer by ID.
     *
     * @param string $id      Customer ID
     * @param array  $options Additional request options
     *
     * @return Customer Customer data
     *
     * @throws MagpieException
     */
    public function retrieve(string $id, array $options = []): mixed
    {
        $customer = parent::retrieve($id, $options);
        $transformedCustomer = $this->transformCustomerResponse($customer);
        return $this->createFromArray($transformedCustomer);
    }

    /**
     * Update a customer.
     *
     * @param string                      $id      Customer ID
     * @param UpdateCustomerRequest|array $request Update parameters or DTO
     * @param array                       $options Additional request options
     *
     * @return Customer Updated customer data
     *
     * @throws MagpieException
     */
    public function update(string $id, UpdateCustomerRequest|array $request, array $options = []): mixed
    {
        if (is_array($request)) {
            $transformedRequest = $this->transformCustomerPayload($request);
            $customer = parent::update($id, $transformedRequest, $options);
            $transformedCustomer = $this->transformCustomerResponse($customer);
            return $this->createFromArray($transformedCustomer);
        }
        
        $transformedRequest = $this->transformCustomerPayload($request->toArray());
        $customer = parent::update($id, $transformedRequest, $options);
        $transformedCustomer = $this->transformCustomerResponse($customer);
        return $this->createFromArray($transformedCustomer);
    }

    /**
     * Retrieve a customer by their email address.
     *
     * @param string $email   The email address of the customer
     * @param array  $options Additional request options
     *
     * @return Customer Customer data
     *
     * @throws MagpieException
     *
     * @example
     * ```php
     * $customer = $magpie->customers->retrieveByEmail('john@example.com');
     * ```
     */
    public function retrieveByEmail(string $email, array $options = []): mixed
    {
        $customer = $this->customAction('GET', $this->buildPath().'/by_email/'.$email, null, $options);
        $transformedCustomer = $this->transformCustomerResponse($customer);
        return $this->createFromArray($transformedCustomer);
    }

    /**
     * Attach a payment source to a customer.
     *
     * @param string $id      The unique identifier of the customer
     * @param string $source  The ID of the payment source to attach
     * @param array  $options Additional request options
     *
     * @return Customer Updated customer data
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
    public function attachSource(string $id, string $source, array $options = []): mixed
    {
        $customer = $this->customResourceAction('POST', $id, 'sources', ['source' => $source], $options);
        $transformedCustomer = $this->transformCustomerResponse($customer);
        return $this->createFromArray($transformedCustomer);
    }

    /**
     * Detach a payment source from a customer.
     *
     * @param string $id      The unique identifier of the customer
     * @param string $source  The ID of the payment source to detach
     * @param array  $options Additional request options
     *
     * @return Customer Updated customer data
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
    public function detachSource(string $id, string $source, array $options = []): mixed
    {
        $customer = $this->customAction('DELETE', $this->buildPath($id, "sources/{$source}"), null, $options);
        $transformedCustomer = $this->transformCustomerResponse($customer);
        return $this->createFromArray($transformedCustomer);
    }

    /**
     * Transform customer payload to move name to metadata.
     *
     * @param array $payload The original payload
     * @return array The transformed payload
     */
    private function transformCustomerPayload(array $payload): array
    {
        if (isset($payload['name'])) {
            // Initialize metadata if not present
            $payload['metadata'] = $payload['metadata'] ?? [];
            
            // Set name in metadata (this will update existing or create new)
            $payload['metadata']['name'] = $payload['name'];
            
            // Remove name from top level
            unset($payload['name']);
        }

        return $payload;
    }

    /**
     * Transform customer response to extract name from metadata.
     *
     * @param array $response The API response
     * @return array The transformed response
     */
    private function transformCustomerResponse(array $response): array
    {
        if (isset($response['metadata']['name'])) {
            // Extract name from metadata to top level
            $response['name'] = $response['metadata']['name'];
        }

        return $response;
    }

    protected function createFromArray(array $data): Customer
    {
        return Customer::fromArray($data);
    }
}
