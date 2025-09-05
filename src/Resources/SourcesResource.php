<?php

declare(strict_types=1);

namespace Magpie\Resources;

use Magpie\Exceptions\MagpieException;
use Magpie\Http\Client;

/**
 * Resource class for managing payment sources.
 *
 * The SourcesResource provides methods to retrieve payment sources that have been
 * securely created through Magpie's frontend SDKs or secure tokenization services.
 *
 * For PCI compliance, raw card data creation is not supported through this server-side SDK.
 * Use Magpie's client-side SDKs to securely collect and tokenize payment information.
 */
class SourcesResource extends BaseResource
{
    public function __construct(Client $client)
    {
        parent::__construct($client, '/sources');
    }

    /**
     * Retrieve an existing payment source by ID.
     *
     * @param string $id      The unique identifier of the source
     * @param array  $options Additional request options
     *
     * @return array Source data
     *
     * @throws MagpieException
     */
    public function retrieve(string $id, array $options = []): array
    {
        return parent::retrieve($id, $options);
    }
}
