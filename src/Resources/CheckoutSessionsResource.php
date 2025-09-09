<?php

declare(strict_types=1);

namespace Magpie\Resources;

use Magpie\Contracts\CheckoutSessionServiceInterface;
use Magpie\DTOs\Requests\Checkout\CaptureSessionRequest;
use Magpie\DTOs\Requests\Checkout\CreateCheckoutSessionRequest;
use Magpie\DTOs\Responses\CheckoutSession;
use Magpie\Exceptions\MagpieException;
use Magpie\Http\Client;

/**
 * Resource class for managing checkout sessions.
 *
 * Checkout sessions provide a hosted payment page where customers can
 * securely enter their payment information. Sessions can be configured
 * with line items, branding, and redirect URLs for success/cancel scenarios.
 */
class CheckoutSessionsResource extends BaseResource implements CheckoutSessionServiceInterface
{
    /**
     * Create a new CheckoutSessionsResource instance.
     *
     * @param Client $client The HTTP client instance for API communication
     */
    public function __construct(Client $client)
    {
        // Use different base URL for checkout sessions with trailing slash
        parent::__construct($client, '', 'https://api.pay.magpie.im/');
    }

    /**
     * Create a new checkout session.
     *
     * Creates a hosted payment page where customers can securely complete their
     * purchase. The session includes line items, payment options, and redirect URLs.
     *
     * @param CreateCheckoutSessionRequest|array $request Checkout session creation parameters or DTO
     * @param array                              $options Additional request options
     *
     * @return mixed Created checkout session data
     *
     * @throws MagpieException
     *
     * @example
     * ```php
     * // Using array (backward compatible)
     * $session = $magpie->checkout->sessions->create([
     *     'line_items' => [
     *         [
     *             'amount' => 25000, // PHP 250.00
     *             'description' => 'Pro Plan Monthly',
     *             'quantity' => 1
     *         ]
     *     ],
     *     'success_url' => 'https://example.com/success',
     *     'cancel_url' => 'https://example.com/cancel',
     *     'customer_email' => 'customer@example.com'
     * ]);
     *
     * // Using DTO (type-safe)
     * $request = new CreateCheckoutSessionRequest(
     *     line_items: [
     *         [
     *             'amount' => 25000,
     *             'description' => 'Pro Plan Monthly',
     *             'quantity' => 1
     *         ]
     *     ],
     *     success_url: 'https://example.com/success',
     *     cancel_url: 'https://example.com/cancel',
     *     customer_email: 'customer@example.com'
     * );
     * $session = $magpie->checkout->sessions->create($request);
     *
     * // Redirect customer to the checkout page
     * header('Location: ' . $session['url']);
     * ```
     */
    public function create(CreateCheckoutSessionRequest|array $request, array $options = []): mixed
    {
        $requestData = is_array($request) ? $request : $request->toArray();

        // Use custom base URL for checkout sessions
        $requestOptions = array_merge($options, [
            'base_uri' => $this->customBaseUrl,
        ]);

        $data = $this->client->post($this->basePath, $requestData, $requestOptions);

        return $this->createFromArray($data);
    }

    /**
     * Retrieve an existing checkout session by ID.
     *
     * @param string $id      The unique identifier of the checkout session
     * @param array  $options Additional request options
     *
     * @return mixed Checkout session data
     *
     * @throws MagpieException
     */
    public function retrieve(string $id, array $options = []): mixed
    {
        // Use custom base URL for checkout sessions
        $requestOptions = array_merge($options, [
            'base_uri' => $this->customBaseUrl,
        ]);

        $data = $this->client->get($this->buildPath($id), null, $requestOptions);

        return $this->createFromArray($data);
    }

    /**
     * Capture payment for a checkout session.
     *
     * For sessions created with authorization-only payment methods,
     * this captures the authorized amount (or a portion of it).
     *
     * @param string                      $id      The unique identifier of the checkout session
     * @param CaptureSessionRequest|array $request The capture parameters or DTO
     * @param array                       $options Additional request options
     *
     * @return mixed Updated checkout session data
     *
     * @throws MagpieException
     */
    public function capture(string $id, CaptureSessionRequest|array $request, array $options = []): mixed
    {
        $requestData = is_array($request) ? $request : $request->toArray();

        // Use custom base URL for checkout sessions
        $requestOptions = array_merge($options, [
            'base_uri' => $this->customBaseUrl,
        ]);

        $data = $this->client->request('POST', $this->buildPath($id, 'capture'), $requestData, $requestOptions);

        return $this->createFromArray($data);
    }

    /**
     * Expire a checkout session, making it no longer usable.
     *
     * Once expired, customers will no longer be able to complete
     * payment through the checkout session URL.
     *
     * @param string $id      The unique identifier of the checkout session to expire
     * @param array  $options Additional request options
     *
     * @return mixed Expired checkout session data
     *
     * @throws MagpieException
     */
    public function expire(string $id, array $options = []): mixed
    {
        // Use custom base URL for checkout sessions
        $requestOptions = array_merge($options, [
            'base_uri' => $this->customBaseUrl,
        ]);

        $data = $this->client->request('POST', $this->buildPath($id, 'expire'), null, $requestOptions);

        return $this->createFromArray($data);
    }

    protected function createFromArray(array $data): CheckoutSession
    {
        return CheckoutSession::fromArray($data);
    }
}
