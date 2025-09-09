<?php

declare(strict_types=1);

namespace Magpie\Resources;

use Magpie\Contracts\PaymentRequestServiceInterface;
use Magpie\DTOs\Requests\PaymentRequests\CreatePaymentRequestRequest;
use Magpie\DTOs\Requests\PaymentRequests\VoidPaymentRequestRequest;
use Magpie\DTOs\Responses\PaymentRequest;
use Magpie\Exceptions\MagpieException;
use Magpie\Http\Client;

/**
 * Resource class for managing payment requests.
 *
 * Payment requests allow you to request payments from customers via email
 * or SMS. They provide a convenient way to collect payments without requiring
 * customers to visit your website or app.
 */
class PaymentRequestsResource extends BaseResource implements PaymentRequestServiceInterface
{
    /**
     * Create a new PaymentRequestsResource instance.
     *
     * @param Client $client The HTTP client instance for API communication
     */
    public function __construct(Client $client)
    {
        parent::__construct($client, 'requests', 'https://request.magpie.im/api/v1/');
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
     * @return mixed Created payment request data
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
    public function create(CreatePaymentRequestRequest|array $request, array $options = []): mixed
    {
        $requestData = is_array($request) ? $request : $request->toArray();

        // Use custom base URL for payment requests
        $requestOptions = array_merge($options, [
            'base_uri' => $this->customBaseUrl,
        ]);

        $data = $this->client->post($this->basePath, $requestData, $requestOptions);

        return $this->createFromArray($data);
    }

    /**
     * Retrieve an existing payment request by ID.
     *
     * @param string $id      The unique identifier of the payment request
     * @param array  $options Additional request options
     *
     * @return mixed Payment request data
     *
     * @throws MagpieException
     */
    public function retrieve(string $id, array $options = []): mixed
    {
        // Use custom base URL for payment requests
        $requestOptions = array_merge($options, [
            'base_uri' => $this->customBaseUrl,
        ]);

        $data = $this->client->get($this->buildPath($id), null, $requestOptions);

        return $this->createFromArray($data);
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
     * @return mixed Payment request data
     *
     * @throws MagpieException
     */
    public function resend(string $id, array $options = []): mixed
    {
        // Use custom base URL for payment requests
        $requestOptions = array_merge($options, [
            'base_uri' => $this->customBaseUrl,
        ]);

        $data = $this->client->request('POST', $this->buildPath($id, 'resend'), null, $requestOptions);

        return $this->createFromArray($data);
    }

    /**
     * Void a payment request, canceling it and preventing payment.
     *
     * Once voided, the customer will no longer be able to pay the request,
     * and any payment attempts will be rejected.
     *
     * @param string                          $id      The unique identifier of the payment request to void
     * @param VoidPaymentRequestRequest|array $request The void parameters or DTO
     * @param array                           $options Additional request options
     *
     * @return mixed Voided payment request data
     *
     * @throws MagpieException
     */
    public function void(string $id, VoidPaymentRequestRequest|array $request, array $options = []): mixed
    {
        $requestData = is_array($request) ? $request : $request->toArray();

        // Use custom base URL for payment requests
        $requestOptions = array_merge($options, [
            'base_uri' => $this->customBaseUrl,
        ]);

        $response = $this->client->request('POST', $this->buildPath($id, 'void'), $requestData, $requestOptions);

        // Void API returns { message: string, data: PaymentRequest }
        // Extract the data property which contains the actual PaymentRequest
        if (is_array($response) && isset($response['data'])) {
            return $this->createFromArray($response['data']);
        }

        // Fallback to direct response if structure is different
        return $this->createFromArray($response);
    }

    protected function createFromArray(array $data): PaymentRequest
    {
        return PaymentRequest::fromArray($data);
    }
}
