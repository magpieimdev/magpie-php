<?php

declare(strict_types=1);

namespace Magpie\Resources;

use Magpie\Exceptions\MagpieException;
use Magpie\Http\Client;
use Magpie\DTOs\Responses\Organization;
use Magpie\Contracts\OrganizationServiceInterface;

/**
 * Resource class for managing organization information.
 *
 * The OrganizationResource provides methods to retrieve organization details
 * including API keys, payment method settings, and configuration.
 */
class OrganizationResource extends BaseResource implements OrganizationServiceInterface
{
    public function __construct(Client $client)
    {
        parent::__construct($client, 'me');
    }

    /**
     * Retrieve the current organization details.
     *
     * This method fetches organization information for the account
     * associated with the current API key, including both test and live
     * public/secret keys.
     *
     * @param array $options Additional request options
     *
     * @return mixed Organization data including keys and settings
     *
     * @throws MagpieException
     */
    public function me(array $options = []): mixed
    {
        $data = $this->client->get($this->basePath, null, $options);
        return $this->createFromArray($data);
    }

    /**
     * Get the appropriate public key based on the secret key environment.
     *
     * @param mixed  $organizationData The organization data from me() (array or Organization object)
     * @param string $secretKey        The current secret key to determine environment
     *
     * @return string The corresponding public key
     *
     * @throws MagpieException
     */
    public function getPublicKey(mixed $organizationData, string $secretKey): string
    {
        // Determine if we're in test or live mode based on the secret key
        $isTestMode = str_contains($secretKey, '_test_');

        $publicKey = $isTestMode
            ? ($organizationData['pk_test_key'] ?? null)
            : ($organizationData['pk_live_key'] ?? null);

        if (null === $publicKey) {
            $mode = $isTestMode ? 'test' : 'live';

            throw new MagpieException("No {$mode} public key available for organization", 'api_error', 'missing_public_key');
        }

        return $publicKey;
    }

    /**
     * Get organization payment method configurations.
     *
     * @param mixed       $organizationData The organization data from me() (array or Organization object)
     * @param string|null $paymentMethod    Optional specific payment method to retrieve
     *
     * @return array Payment method settings (all methods or specific method)
     */
    public function getPaymentMethods(mixed $organizationData, ?string $paymentMethod = null): array
    {
        $allSettings = $organizationData['payment_method_settings'] ?? [];

        if (null === $paymentMethod) {
            return $allSettings;
        }

        return $allSettings[$paymentMethod] ?? [];
    }

    /**
     * Check if a payment method is enabled for the organization.
     *
     * @param mixed  $organizationData The organization data from me() (array or Organization object)
     * @param string $paymentMethod    The payment method to check
     *
     * @return bool True if the payment method is approved and enabled
     */
    public function isPaymentMethodEnabled(mixed $organizationData, string $paymentMethod): bool
    {
        $settings = $this->getPaymentMethods($organizationData, $paymentMethod);

        return ! empty($settings) && 'approved' === ($settings['status'] ?? null);
    }

    /**
     * Get organization branding configuration.
     *
     * @param mixed $organizationData The organization data from me() (array or Organization object)
     *
     * @return array branding settings including logo, colors, etc
     */
    public function getBranding(mixed $organizationData): array
    {
        return $organizationData['branding'] ?? [];
    }

    /**
     * Get organization payout settings.
     *
     * @param mixed $organizationData The organization data from me() (array or Organization object)
     *
     * @return array Payout configuration including schedule and bank details
     */
    public function getPayoutSettings(mixed $organizationData): array
    {
        return $organizationData['payout_settings'] ?? [];
    }

    protected function createFromArray(array $data): Organization
    {
        return Organization::fromArray($data);
    }
}
