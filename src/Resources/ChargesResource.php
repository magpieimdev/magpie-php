<?php

declare(strict_types=1);

namespace Magpie\Resources;

use Magpie\Exceptions\MagpieException;
use Magpie\Http\Client;

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
        parent::__construct($client, '/charges');
    }

    /**
     * Create a new charge.
     *
     * A charge represents a request to transfer money from a customer to your account.
     * You can create charges directly or authorize them first for later capture.
     *
     * @param array $params  The parameters for creating the charge
     * @param array $options Additional request options (e.g., idempotency key)
     *
     * @return array The created charge data
     *
     * @throws MagpieException
     *
     * @example
     * ```php
     * $charge = $magpie->charges->create([
     *     'amount' => 20000, // 200.00 in centavos
     *     'currency' => 'php',
     *     'source' => 'src_1234567890',
     *     'description' => 'Payment for order #1001',
     *     'capture' => true // Capture immediately
     * ]);
     * ```
     */
    public function create(array $params, array $options = []): array
    {
        return parent::create($params, $options);
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
     * @param string $id      The unique identifier of the charge to capture
     * @param array  $params  The capture parameters (amount, etc.)
     * @param array  $options Additional request options
     *
     * @return array The updated charge data
     *
     * @throws MagpieException
     *
     * @example
     * ```php
     * // Capture the full amount
     * $captured = $magpie->charges->capture('ch_1234567890', [
     *     'amount' => 20000
     * ]);
     *
     * // Or capture a partial amount
     * $partialCapture = $magpie->charges->capture('ch_1234567890', [
     *     'amount' => 15000 // Capture 150.00 instead of 200.00
     * ]);
     * ```
     */
    public function capture(string $id, array $params = [], array $options = []): array
    {
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
     * @param string $id      The unique identifier of the charge to refund
     * @param array  $params  The refund parameters (amount, reason, etc.)
     * @param array  $options Additional request options
     *
     * @return array The updated charge data with refund information
     *
     * @throws MagpieException
     *
     * @example
     * ```php
     * // Full refund
     * $refunded = $magpie->charges->refund('ch_1234567890', [
     *     'amount' => 20000,
     *     'reason' => 'requested_by_customer'
     * ]);
     *
     * // Partial refund
     * $partialRefund = $magpie->charges->refund('ch_1234567890', [
     *     'amount' => 10000, // Refund 100.00 out of 200.00 charge
     *     'reason' => 'duplicate'
     * ]);
     * ```
     */
    public function refund(string $id, array $params = [], array $options = []): array
    {
        return $this->customResourceAction('POST', $id, 'refund', $params, $options);
    }
}
