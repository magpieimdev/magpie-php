<?php

declare(strict_types=1);

namespace Magpie\Resources;

use Magpie\Exceptions\MagpieException;
use Magpie\Http\Client;

/**
 * Resource class for managing payment links.
 *
 * Payment links are shareable URLs that allow you to collect payments from
 * customers without building a custom checkout flow. They can be sent via
 * email, SMS, or shared on social media.
 */
class PaymentLinksResource extends BaseResource
{
    /**
     * Create a new PaymentLinksResource instance.
     *
     * @param Client $client The HTTP client instance for API communication
     */
    public function __construct(Client $client)
    {
        parent::__construct($client, '/links', 'https://buy.magpie.im/api/v1');
    }

    /**
     * Create a new payment link.
     *
     * Payment links provide a hosted payment page accessible via a shareable URL.
     * No coding required - perfect for social media, email campaigns, or instant invoicing.
     *
     * @param array $params  The parameters for creating the payment link
     * @param array $options Additional request options
     *
     * @return array Created payment link data
     *
     * @throws MagpieException
     *
     * @example
     * ```php
     * $paymentLink = $magpie->paymentLinks->create([
     *     'internal_name' => 'Website Design Service',
     *     'allow_adjustable_quantity' => true,
     *     'line_items' => [
     *         [
     *             'amount' => 100000, // PHP 1,000.00
     *             'description' => 'Website Design Service',
     *             'quantity' => 1,
     *             'image' => 'https://example.com/service.jpg'
     *         ]
     *     ]
     * ]);
     * ```
     */
    public function create(array $params, array $options = []): array
    {
        return parent::create($params, $options);
    }

    /**
     * Retrieve an existing payment link by ID.
     *
     * @param string $id      The unique identifier of the payment link
     * @param array  $options Additional request options
     *
     * @return array Payment link data
     *
     * @throws MagpieException
     */
    public function retrieve(string $id, array $options = []): array
    {
        return parent::retrieve($id, $options);
    }

    /**
     * Update an existing payment link.
     *
     * @param string $id      The unique identifier of the payment link to update
     * @param array  $params  The update parameters
     * @param array  $options Additional request options
     *
     * @return array Updated payment link data
     *
     * @throws MagpieException
     */
    public function update(string $id, array $params, array $options = []): array
    {
        return parent::update($id, $params, $options);
    }

    /**
     * Activate a payment link, making it available for payments.
     *
     * @param string $id      The unique identifier of the payment link to activate
     * @param array  $options Additional request options
     *
     * @return array Activated payment link data
     *
     * @throws MagpieException
     */
    public function activate(string $id, array $options = []): array
    {
        return $this->customResourceAction('POST', $id, 'activate', null, $options);
    }

    /**
     * Deactivate a payment link, preventing new payments.
     *
     * Once deactivated, customers will no longer be able to complete
     * payments through this payment link.
     *
     * @param string $id      The unique identifier of the payment link to deactivate
     * @param array  $options Additional request options
     *
     * @return array Deactivated payment link data
     *
     * @throws MagpieException
     */
    public function deactivate(string $id, array $options = []): array
    {
        return $this->customResourceAction('POST', $id, 'deactivate', null, $options);
    }
}
