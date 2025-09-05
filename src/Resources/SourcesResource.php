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
 *
 * Note: This resource automatically switches from secret key to public key authentication
 * for accessing source data.
 */
class SourcesResource extends BaseResource
{
    /**
     * Whether PK authentication has been initialized.
     */
    private bool $pkInitialized = false;

    public function __construct(Client $client)
    {
        parent::__construct($client, '/sources');
    }

    /**
     * Switch the client to use public key authentication.
     *
     * This method fetches organization details and switches the client to use
     * the appropriate public key.
     *
     * @throws MagpieException
     */
    private function ensurePKAuthentication(): void
    {
        if ($this->pkInitialized) {
            return;
        }

        // Only switch if we're currently using a secret key
        if (! str_starts_with($this->client->getApiKey(), 'sk_')) {
            $this->pkInitialized = true;

            return;
        }

        $secretKey = $this->client->getApiKey();

        // Fetch organization data to get public key
        $organizationResource = new OrganizationResource($this->client);
        $organizationData = $organizationResource->me();

        // Get the appropriate public key
        $publicKey = $organizationResource->getPublicKey($organizationData, $secretKey);

        // Switch client to use public key
        $this->client->setApiKey($publicKey);
        $this->pkInitialized = true;
    }

    /**
     * Ensure we're using public key authentication before making requests.
     *
     * @throws MagpieException
     */
    private function ensurePublicKeyAuthentication(): void
    {
        if (! str_starts_with($this->client->getApiKey(), 'pk_')) {
            throw new MagpieException('SourcesResource requires public key authentication for security compliance', 'authentication_error', 'invalid_key_type');
        }
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
        $this->ensurePKAuthentication();
        $this->ensurePublicKeyAuthentication();

        return parent::retrieve($id, $options);
    }
}
