<?php

declare(strict_types=1);

namespace Magpie\Resources;

use Magpie\Exceptions\MagpieException;
use Magpie\Http\Client;
use Magpie\DTOs\Requests\PaymentLinks\CreatePaymentLinkRequest;
use Magpie\DTOs\Requests\PaymentLinks\UpdatePaymentLinkRequest;
use Magpie\DTOs\Responses\PaymentLink;
use Magpie\Contracts\PaymentLinkServiceInterface;

/**
 * Resource class for managing payment links.
 *
 * Payment links are shareable URLs that allow you to collect payments from
 * customers without building a custom checkout flow. They can be sent via
 * email, SMS, or shared on social media.
 */
class PaymentLinksResource extends BaseResource implements PaymentLinkServiceInterface
{
    /**
     * Create a new PaymentLinksResource instance.
     *
     * @param Client $client The HTTP client instance for API communication
     */
    public function __construct(Client $client)
    {
        parent::__construct($client, 'links', 'https://buy.magpie.im/api/v1/');
    }

    /**
     * Create a new payment link.
     *
     * Payment links provide a hosted payment page accessible via a shareable URL.
     * No coding required - perfect for social media, email campaigns, or instant invoicing.
     *
     * @param CreatePaymentLinkRequest|array $request  Payment link creation parameters or DTO
     * @param array                          $options  Additional request options
     *
     * @return mixed Created payment link data
     *
     * @throws MagpieException
     *
     * @example
     * ```php
     * // Using array (backward compatible)
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
     *
     * // Using DTO (type-safe)
     * $request = new CreatePaymentLinkRequest(
     *     allow_adjustable_quantity: true,
     *     currency: 'PHP',
     *     internal_name: 'Website Design Service',
     *     line_items: [
     *         [
     *             'amount' => 100000,
     *             'description' => 'Website Design Service',
     *             'quantity' => 1
     *         ]
     *     ],
     *     payment_method_types: ['card']
     * );
     * $paymentLink = $magpie->paymentLinks->create($request);
     * ```
     */
    public function create(CreatePaymentLinkRequest|array $request, array $options = []): mixed
    {
        if (is_array($request)) {
            $data = parent::create($request, $options);
        } else {
            $data = parent::create($request->toArray(), $options);
        }
        
        return $this->createFromArray($data);
    }

    /**
     * Retrieve an existing payment link by ID.
     *
     * @param string $id      The unique identifier of the payment link
     * @param array  $options Additional request options
     *
     * @return mixed Payment link data
     *
     * @throws MagpieException
     */
    public function retrieve(string $id, array $options = []): mixed
    {
        $data = parent::retrieve($id, $options);
        return $this->createFromArray($data);
    }

    /**
     * Update an existing payment link.
     *
     * @param string                         $id      The unique identifier of the payment link to update
     * @param UpdatePaymentLinkRequest|array $request The update parameters or DTO
     * @param array                          $options Additional request options
     *
     * @return mixed Updated payment link data
     *
     * @throws MagpieException
     */
    public function update(string $id, UpdatePaymentLinkRequest|array $request, array $options = []): mixed
    {
        if (is_array($request)) {
            $data = parent::update($id, $request, $options);
        } else {
            $data = parent::update($id, $request->toArray(), $options);
        }
        
        return $this->createFromArray($data);
    }

    /**
     * Activate a payment link, making it available for payments.
     *
     * @param string $id      The unique identifier of the payment link to activate
     * @param array  $options Additional request options
     *
     * @return mixed Activated payment link data
     *
     * @throws MagpieException
     */
    public function activate(string $id, array $options = []): mixed
    {
        $data = $this->customResourceAction('POST', $id, 'activate', null, $options);
        return $this->createFromArray($data);
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
     * @return mixed Deactivated payment link data
     *
     * @throws MagpieException
     */
    public function deactivate(string $id, array $options = []): mixed
    {
        $data = $this->customResourceAction('POST', $id, 'deactivate', null, $options);
        return $this->createFromArray($data);
    }

    protected function createFromArray(array $data): PaymentLink
    {
        return PaymentLink::fromArray($data);
    }
}
