<?php

declare(strict_types=1);

namespace Magpie\Resources;

use Magpie\Exceptions\MagpieException;
use Magpie\Http\Client;
use Magpie\DTOs\Requests\PaymentRequests\CreatePaymentRequestRequest;
use Magpie\DTOs\Requests\PaymentRequests\VoidPaymentRequestRequest;

/**
 * Resource class for managing payment requests.
 *
 * Payment requests allow you to request payments from customers via email
 * or SMS. They provide a convenient way to collect payments without requiring
 * customers to visit your website or app.
 */
class PaymentRequestsResource extends BaseResource
{
    /**
     * Create a new PaymentRequestsResource instance.
     *
     * @param Client $client The HTTP client instance for API communication
     */
    public function __construct(Client $client)
    {
        parent::__construct($client, 'requests', 'https://request.magpie.im/api/v1');
    }

    /**
     * Create a new payment request.
     *
     * Sends a payment request to a customer via email, SMS, or both.
     * The customer receives a link to a secure payment page where they
     * can complete the payment.
     *
     * @param CreatePaymentRequestRequest|array $request Payment request creation parameters or DTO
     * @param array                             $options Additional request options
     *
     * @return array Created payment request data
     *
     * @throws MagpieException
     *
     * @example
     * ```php
     * // Using array (backward compatible)
     * $request = $magpie->paymentRequests->create([
     *     'amount' => 50000, // PHP 500.00
     *     'currency' => 'php',
     *     'description' => 'Monthly Subscription Payment',
     *     'recipient' => [
     *         'name' => 'Jane Smith',
     *         'email' => 'jane@example.com',
     *         'phone' => '+639151234567'
     *     ],
     *     'send_email' => true,
     *     'send_sms' => true
     * ]);
     *
     * // Using DTO (type-safe)
     * $request = new CreatePaymentRequestRequest(
     *     amount: 50000,
     *     currency: 'php',
     *     description: 'Monthly Subscription Payment',
     *     recipient: [
     *         'name' => 'Jane Smith',
     *         'email' => 'jane@example.com',
     *         'phone' => '+639151234567'
     *     ],
     *     send_email: true,
     *     send_sms: true
     * );
     * $paymentRequest = $magpie->paymentRequests->create($request);
     * ```
     */
    public function create(CreatePaymentRequestRequest|array $request, array $options = []): array
    {
        if (is_array($request)) {
            return parent::create($request, $options);
        }
        
        return parent::create($request->toArray(), $options);
    }

    /**
     * Retrieve an existing payment request by ID.
     *
     * @param string $id      The unique identifier of the payment request
     * @param array  $options Additional request options
     *
     * @return array Payment request data
     *
     * @throws MagpieException
     */
    public function retrieve(string $id, array $options = []): array
    {
        return parent::retrieve($id, $options);
    }

    /**
     * Resend a payment request to the customer.
     *
     * Sends the payment request notification again via the originally
     * configured channels (email, SMS, or both).
     *
     * @param string $id      The unique identifier of the payment request to resend
     * @param array  $options Additional request options
     *
     * @return array Payment request data
     *
     * @throws MagpieException
     */
    public function resend(string $id, array $options = []): array
    {
        return $this->customResourceAction('POST', $id, 'resend', null, $options);
    }

    /**
     * Void a payment request, canceling it and preventing payment.
     *
     * Once voided, the customer will no longer be able to pay the request,
     * and any payment attempts will be rejected.
     *
     * @param string                        $id      The unique identifier of the payment request to void
     * @param VoidPaymentRequestRequest|array $request The void parameters or DTO
     * @param array                         $options Additional request options
     *
     * @return array Voided payment request data
     *
     * @throws MagpieException
     */
    public function void(string $id, VoidPaymentRequestRequest|array $request, array $options = []): array
    {
        if (is_array($request)) {
            return $this->customResourceAction('POST', $id, 'void', $request, $options);
        }
        
        return $this->customResourceAction('POST', $id, 'void', $request->toArray(), $options);
    }
}
