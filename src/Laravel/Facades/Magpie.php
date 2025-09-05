<?php

declare(strict_types=1);

namespace Magpie\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Laravel facade for the Magpie SDK.
 *
 * This facade provides a static interface to the Magpie SDK,
 * making it easy to use within Laravel applications.
 *
 * @example
 * ```php
 * use Magpie\Laravel\Facades\Magpie;
 *
 * // Create a customer
 * $customer = Magpie::customers()->create([
 *     'name' => 'John Doe',
 *     'email' => 'john@example.com'
 * ]);
 *
 * // Create a charge
 * $charge = Magpie::charges()->create([
 *     'amount' => 10000,
 *     'currency' => 'php',
 *     'source' => $source['id']
 * ]);
 * ```
 *
 * @method static \Magpie\Resources\ChargesResource         charges()
 * @method static \Magpie\Resources\CustomersResource       customers()
 * @method static \Magpie\Resources\OrganizationResource    organization()
 * @method static \Magpie\Resources\SourcesResource         sources()
 * @method static \Magpie\Resources\CheckoutResource        checkout()
 * @method static \Magpie\Resources\PaymentRequestsResource paymentRequests()
 * @method static \Magpie\Resources\PaymentLinksResource    paymentLinks()
 * @method static \Magpie\Resources\WebhooksResource        webhooks()
 * @method static \Magpie\Http\Client                       getClient()
 * @method static \Magpie\Http\Config                       getConfig()
 * @method static bool                                      ping()
 * @method static void                                      setDebug(bool $debug)
 * @method static string                                    getApiVersion()
 * @method static string                                    getBaseUrl()
 *
 * @see \Magpie\Magpie
 */
class Magpie extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'magpie';
    }
}
