<?php

declare(strict_types=1);

namespace Magpie\Resources;

use Magpie\Exceptions\MagpieException;
use Magpie\Http\Client;

/**
 * Resource class for managing organization information.
 *
 * The OrganizationResource provides methods to retrieve organization details
 * including API keys, payment method settings, and configuration.
 */
class OrganizationResource extends BaseResource
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
     * @return array Organization data including keys and settings
     *
     * @throws MagpieException
     */
    public function me(array $options = []): array
    {
        return $this->client->get($this->basePath, null, $options);
    }

    /**
     * Get the appropriate public key based on the secret key environment.
     *
     * @param array  $organizationData The organization data from me()
     * @param string $secretKey        The current secret key to determine environment
     *
     * @return string The corresponding public key
     *
     * @throws MagpieException
     */
    public function getPublicKey(array $organizationData, string $secretKey): string
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
     * @param array       $organizationData The organization data from me()
     * @param string|null $paymentMethod    Optional specific payment method to retrieve
     *
     * @return array Payment method settings (all methods or specific method)
     */
    public function getPaymentMethods(array $organizationData, ?string $paymentMethod = null): array
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
     * @param array  $organizationData The organization data from me()
     * @param string $paymentMethod    The payment method to check
     *
     * @return bool True if the payment method is approved and enabled
     */
    public function isPaymentMethodEnabled(array $organizationData, string $paymentMethod): bool
    {
        $settings = $this->getPaymentMethods($organizationData, $paymentMethod);

        return ! empty($settings) && 'approved' === ($settings['status'] ?? null);
    }

    /**
     * Get organization branding configuration.
     *
     * @param array $organizationData The organization data from me()
     *
     * @return array branding settings including logo, colors, etc
     */
    public function getBranding(array $organizationData): array
    {
        return $organizationData['branding'] ?? [];
    }

    /**
     * Get organization payout settings.
     *
     * @param array $organizationData The organization data from me()
     *
     * @return array Payout configuration including schedule and bank details
     */
    public function getPayoutSettings(array $organizationData): array
    {
        return $organizationData['payout_settings'] ?? [];
    }
}
