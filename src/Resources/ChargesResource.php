<?php

declare(strict_types=1);

namespace Magpie\Resources;

use Magpie\Exceptions\MagpieException;
use Magpie\Http\Client;
use Magpie\DTOs\Requests\Charges\CreateChargeRequest;
use Magpie\DTOs\Requests\Charges\CaptureChargeRequest;
use Magpie\DTOs\Requests\Charges\RefundChargeRequest;

/**
 * Resource class for managing charges and payments.
 *
 * The ChargesResource provides methods to create, retrieve, and manipulate charges
 * in the Magpie payment system. This includes creating new charges, capturing
 * authorized payments, processing refunds, and verifying payment authenticity.
 */
class ChargesResource extends BaseResource
{
    /**
     * Create a new ChargesResource instance.
     *
     * @param Client $client The HTTP client instance for API communication
     */
    public function __construct(Client $client)
    {
        parent::__construct($client, 'charges');
    }

    /**
     * Create a new charge.
     *
     * A charge represents a request to transfer money from a customer to your account.
     * You can create charges directly or authorize them first for later capture.
     *
     * @param CreateChargeRequest|array $request The parameters for creating the charge or DTO
     * @param array                     $options Additional request options (e.g., idempotency key)
     *
     * @return array The created charge data
     *
     * @throws MagpieException
     *
     * @example
     * ```php
     * // Using array (backward compatible)
     * $charge = $magpie->charges->create([
     *     'amount' => 20000, // 200.00 in centavos
     *     'currency' => 'php',
     *     'source' => 'src_1234567890',
     *     'description' => 'Payment for order #1001',
     *     'statement_descriptor' => 'STORE',
     *     'capture' => true // Capture immediately
     * ]);
     *
     * // Using DTO (type-safe)
     * $request = new CreateChargeRequest(
     *     amount: 20000,
     *     currency: 'php',
     *     source: 'src_1234567890',
     *     description: 'Payment for order #1001',
     *     statement_descriptor: 'STORE'
     * );
     * $charge = $magpie->charges->create($request);
     * ```
     */
    public function create(CreateChargeRequest|array $request, array $options = []): array
    {
        if (is_array($request)) {
            return parent::create($request, $options);
        }
        
        return parent::create($request->toArray(), $options);
    }

    /**
     * Retrieve an existing charge by its ID.
     *
     * @param string $id      The unique identifier of the charge to retrieve
     * @param array  $options Additional request options
     *
     * @return array The charge data
     *
     * @throws MagpieException
     *
     * @example
     * ```php
     * $charge = $magpie->charges->retrieve('ch_1234567890');
     * ```
     */
    public function retrieve(string $id, array $options = []): array
    {
        return parent::retrieve($id, $options);
    }

    /**
     * Capture a previously authorized charge.
     *
     * When you create a charge with `capture: false`, it will be authorized but not
     * captured. Use this method to capture the authorized amount (or a portion of it).
     *
     * @param string                      $id      The unique identifier of the charge to capture
     * @param CaptureChargeRequest|array  $request The capture parameters (amount, etc.) or DTO
     * @param array                       $options Additional request options
     *
     * @return array The updated charge data
     *
     * @throws MagpieException
     *
     * @example
     * ```php
     * // Using array (backward compatible)
     * $captured = $magpie->charges->capture('ch_1234567890', [
     *     'amount' => 20000
     * ]);
     *
     * // Using DTO (type-safe)
     * $request = new CaptureChargeRequest(amount: 15000);
     * $captured = $magpie->charges->capture('ch_1234567890', $request);
     * ```
     */
    public function capture(string $id, CaptureChargeRequest|array $request, array $options = []): array
    {
        $params = is_array($request) ? $request : $request->toArray();
        return $this->customResourceAction('POST', $id, 'capture', $params, $options);
    }

    /**
     * Verify a charge with additional authentication data.
     *
     * This method is used for direct bank payments where additional customer
     * authentication is required.
     *
     * @param string $id      The unique identifier of the charge to verify
     * @param array  $params  The verification parameters
     * @param array  $options Additional request options
     *
     * @return array The verified charge data
     *
     * @throws MagpieException
     *
     * @example
     * ```php
     * $verified = $magpie->charges->verify('ch_1234567890', [
     *     'confirmation_id' => '1234567890',
     *     'otp' => '123456'
     * ]);
     * ```
     */
    public function verify(string $id, array $params, array $options = []): array
    {
        return $this->customResourceAction('POST', $id, 'verify', $params, $options);
    }

    /**
     * Void a charge, canceling it before it can be captured.
     *
     * This method can only be used on charges that have been authorized but not yet
     * captured. Once voided, the authorization is released and cannot be captured.
     *
     * @param string $id      The unique identifier of the charge to void
     * @param array  $options Additional request options
     *
     * @return array The voided charge data
     *
     * @throws MagpieException
     *
     * @example
     * ```php
     * $voided = $magpie->charges->void('ch_1234567890');
     * ```
     */
    public function void(string $id, array $options = []): array
    {
        return $this->customResourceAction('POST', $id, 'void', null, $options);
    }

    /**
     * Create a refund for a charge.
     *
     * Refunds can be created for the full charge amount or a partial amount.
     * The refund will be processed back to the original payment method.
     *
     * @param string                     $id      The unique identifier of the charge to refund
     * @param RefundChargeRequest|array  $request The refund parameters (amount, reason, etc.) or DTO
     * @param array                      $options Additional request options
     *
     * @return array The updated charge data with refund information
     *
     * @throws MagpieException
     *
     * @example
     * ```php
     * // Using array (backward compatible)
     * $refunded = $magpie->charges->refund('ch_1234567890', [
     *     'amount' => 20000,
     *     'reason' => 'requested_by_customer'
     * ]);
     *
     * // Using DTO (type-safe)
     * $request = new RefundChargeRequest(
     *     amount: 10000,
     *     reason: 'duplicate'
     * );
     * $refunded = $magpie->charges->refund('ch_1234567890', $request);
     * ```
     */
    public function refund(string $id, RefundChargeRequest|array $request, array $options = []): array
    {
        $params = is_array($request) ? $request : $request->toArray();
        return $this->customResourceAction('POST', $id, 'refund', $params, $options);
    }
}
