<?php

declare(strict_types=1);

namespace Magpie\Resources;

use Magpie\Exceptions\MagpieException;
use Magpie\Http\Client;

/**
 * Base class for all API resource classes.
 *
 * This class provides common functionality for all API resources including
 * standard CRUD operations, list operations, and request handling. It serves
 * as the foundation for specific resource classes like ChargesResource,
 * CustomersResource, etc.
 */
abstract class BaseResource
{
    /**
     * The HTTP client for API communication.
     */
    protected Client $client;

    /**
     * The base path for this resource.
     */
    protected string $basePath;

    /**
     * Custom base URL for this resource (if different from default).
     */
    protected ?string $customBaseUrl;

    /**
     * Create a new resource instance.
     *
     * @param Client      $client        HTTP client instance
     * @param string      $basePath      Base API path for this resource
     * @param string|null $customBaseUrl Custom base URL for this resource
     */
    public function __construct(Client $client, string $basePath, ?string $customBaseUrl = null)
    {
        $this->client = $client;
        $this->basePath = ltrim($basePath, '/');
        $this->customBaseUrl = $customBaseUrl;
    }

    /**
     * Build the full path for a resource operation.
     *
     * @param string|null $id     Resource ID to append
     * @param string|null $action Additional action to append
     */
    protected function buildPath(?string $id = null, ?string $action = null): string
    {
        $path = $this->basePath;

        if (null !== $id) {
            $path .= '/'.$id;
        }

        if (null !== $action) {
            $path .= '/'.ltrim($action, '/');
        }

        return $path;
    }

    /**
     * Create a new resource.
     *
     * @param array $data    Resource creation data
     * @param array $options Additional request options
     *
     * @return array Created resource data
     *
     * @throws MagpieException
     */
    protected function create(array $data, array $options = []): array
    {
        return $this->client->post($this->basePath, $data, $options);
    }

    /**
     * Retrieve a resource by ID.
     *
     * @param string $id      Resource ID
     * @param array  $options Additional request options
     *
     * @return array Resource data
     *
     * @throws MagpieException
     */
    protected function retrieve(string $id, array $options = []): array
    {
        return $this->client->get($this->buildPath($id), null, $options);
    }

    /**
     * Update a resource by ID.
     *
     * @param string $id      Resource ID
     * @param array  $data    Update data
     * @param array  $options Additional request options
     *
     * @return array Updated resource data
     *
     * @throws MagpieException
     */
    protected function update(string $id, array $data, array $options = []): array
    {
        return $this->client->patch($this->buildPath($id), $data, $options);
    }

    /**
     * Delete a resource by ID.
     *
     * @param string $id      Resource ID
     * @param array  $options Additional request options
     *
     * @return array Deletion result
     *
     * @throws MagpieException
     */
    protected function delete(string $id, array $options = []): array
    {
        return $this->client->delete($this->buildPath($id), null, $options);
    }

    /**
     * List resources with optional filtering and pagination.
     *
     * @param array $params  List parameters (filters, pagination, etc.)
     * @param array $options Additional request options
     *
     * @return array List response with data and pagination info
     *
     * @throws MagpieException
     */
    protected function list(array $params = [], array $options = []): array
    {
        return $this->client->get($this->basePath, $params, $options);
    }

    /**
     * Perform a custom action on a resource.
     *
     * @param string     $method  HTTP method
     * @param string     $path    Full path for the action
     * @param array|null $data    Request data
     * @param array      $options Additional request options
     *
     * @return array Response data
     *
     * @throws MagpieException
     */
    protected function customAction(string $method, string $path, ?array $data = null, array $options = []): array
    {
        return $this->client->request($method, $path, $data, $options);
    }

    /**
     * Perform a custom action on a specific resource.
     *
     * @param string     $method  HTTP method
     * @param string     $id      Resource ID
     * @param string     $action  Action name
     * @param array|null $data    Request data
     * @param array      $options Additional request options
     *
     * @return array Response data
     *
     * @throws MagpieException
     */
    protected function customResourceAction(string $method, string $id, string $action, ?array $data = null, array $options = []): array
    {
        return $this->client->request($method, $this->buildPath($id, $action), $data, $options);
    }

    /**
     * Get the HTTP client instance.
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * Get the base path for this resource.
     */
    public function getBasePath(): string
    {
        return $this->basePath;
    }

    /**
     * Build query parameters for list requests.
     *
     * @param array $params Raw parameters
     *
     * @return array Formatted query parameters
     */
    protected function buildListParams(array $params): array
    {
        $queryParams = [];

        // Handle pagination
        if (isset($params['limit'])) {
            $queryParams['limit'] = (int) $params['limit'];
        }

        if (isset($params['offset'])) {
            $queryParams['offset'] = (int) $params['offset'];
        }

        if (isset($params['cursor'])) {
            $queryParams['cursor'] = $params['cursor'];
        }

        // Handle date range filters
        foreach (['created_at', 'updated_at'] as $dateField) {
            if (isset($params[$dateField])) {
                $dateFilter = $params[$dateField];

                if (is_array($dateFilter)) {
                    foreach (['gt', 'gte', 'lt', 'lte'] as $operator) {
                        if (isset($dateFilter[$operator])) {
                            $queryParams["{$dateField}[{$operator}]"] = $dateFilter[$operator];
                        }
                    }
                } else {
                    $queryParams[$dateField] = $dateFilter;
                }
            }
        }

        // Handle expand parameters
        if (isset($params['expand']) && is_array($params['expand'])) {
            $queryParams['expand'] = $params['expand'];
        }

        // Include any other parameters as-is
        foreach ($params as $key => $value) {
            if (! in_array($key, ['limit', 'offset', 'cursor', 'created_at', 'updated_at', 'expand'])) {
                $queryParams[$key] = $value;
            }
        }

        return $queryParams;
    }
}
