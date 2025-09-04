<?php

declare(strict_types=1);

namespace Magpie\Resources;

use Magpie\Exceptions\MagpieException;
use Magpie\Http\Client;

/**
 * Resource class for managing payment sources.
 *
 * The SourcesResource provides methods to create and retrieve payment sources
 * such as credit cards, debit cards, and bank accounts. Sources represent
 * payment methods that can be attached to customers or used for one-time payments.
 */
class SourcesResource extends BaseResource
{
    public function __construct(Client $client)
    {
        parent::__construct($client, '/sources');
    }

    /**
     * Create a new payment source.
     *
     * Payment sources represent payment methods like credit cards or bank accounts
     * that can be used to process payments. Sources can be reusable or single-use.
     *
     * @param array $params The parameters for creating the source
     * @param array $options Additional request options
     * @return array Created source data
     * @throws MagpieException
     * 
     * @example
     * ```php
     * // Create a card source
     * $cardSource = $magpie->sources->create([
     *     'type' => 'card',
     *     'card' => [
     *         'name' => 'John Doe',
     *         'number' => '4242424242424242',
     *         'exp_month' => '12',
     *         'exp_year' => '2025',
     *         'cvc' => '123'
     *     ],
     *     'redirect' => [
     *         'success' => 'https://example.com/success',
     *         'fail' => 'https://example.com/fail'
     *     ]
     * ]);
     * ```
     */
    public function create(array $params, array $options = []): array
    {
        return parent::create($params, $options);
    }

    /**
     * Retrieve an existing payment source by ID.
     *
     * @param string $id The unique identifier of the source
     * @param array $options Additional request options
     * @return array Source data
     * @throws MagpieException
     */
    public function retrieve(string $id, array $options = []): array
    {
        return parent::retrieve($id, $options);
    }
}
