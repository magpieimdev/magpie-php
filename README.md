# Magpie PHP Library

The official Magpie PHP library for seamless integration with Magpie's payment processing APIs. Built for modern PHP applications and Laravel, with comprehensive support for all Magpie API features.

[![Latest Version](https://img.shields.io/packagist/v/magpieim/magpie-php.svg?style=flat-square)](https://packagist.org/packages/magpieim/magpie-php)
[![PHP Version](https://img.shields.io/packagist/php-v/magpieim/magpie-php.svg?style=flat-square)](https://packagist.org/packages/magpieim/magpie-php)
[![Total Downloads](https://img.shields.io/packagist/dt/magpieim/magpie-php.svg?style=flat-square)](https://packagist.org/packages/magpieim/magpie-php)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg?style=flat-square)](https://opensource.org/licenses/MIT)

## Features

- **💳 Complete Payment Processing**: Cards, bank transfers, e-wallets (GCash, Maya, etc.)
- **🔒 Secure by Design**: Built-in authentication, SSL verification, and secure error handling
- **⚡ Laravel Integration**: Service provider, facades, and configuration publishing
- **🔄 Automatic Retries**: Exponential backoff with jitter for transient failures
- **📝 Comprehensive Logging**: Debug mode with request/response logging
- **🛡️ Type Safety**: Full PHP 8.1+ type declarations and comprehensive error handling
- **🌐 Modern HTTP Client**: Built on Guzzle with middleware support

## Requirements

- PHP 8.1 or higher
- Laravel 10+ (for Laravel integration features)
- Guzzle HTTP client

## Installation

Install the package using Composer:

```bash
composer require magpieim/magpie-php
```

### Laravel Integration

If you're using Laravel, the service provider will be auto-registered. Publish the configuration file:

```bash
php artisan vendor:publish --provider="Magpie\Laravel\MagpieServiceProvider" --tag="magpie-config"
```

Add your Magpie credentials to your `.env` file:

```env
MAGPIE_SECRET_KEY=sk_test_your_secret_key_here
MAGPIE_DEBUG=false
```

## Quick Start

### Basic PHP Usage

```php
<?php

require_once 'vendor/autoload.php';

use Magpie\Magpie;

// Initialize the client
$magpie = new Magpie('sk_test_your_secret_key_here');

// Create a customer
$customer = $magpie->customers->create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'phone' => '+639171234567'
]);

// Create a payment source (credit card)
$source = $magpie->sources->create([
    'type' => 'card',
    'card' => [
        'name' => 'John Doe',
        'number' => '4242424242424242',
        'exp_month' => '12',
        'exp_year' => '2025',
        'cvc' => '123'
    ],
    'redirect' => [
        'success' => 'https://example.com/success',
        'fail' => 'https://example.com/fail'
    ]
]);

// Create a charge
$charge = $magpie->charges->create([
    'amount' => 50000, // ₱500.00 in centavos
    'currency' => 'php',
    'source' => $source['id'],
    'description' => 'Payment for Order #1234'
]);

echo "Charge created: " . $charge['id'] . "\n";
```

### Laravel Usage

```php
<?php

use Magpie\Laravel\Facades\Magpie;

// Using the facade
$customer = Magpie::customers()->create([
    'name' => 'Jane Doe',
    'email' => 'jane@example.com'
]);

// Or dependency injection
class PaymentController extends Controller
{
    public function createPayment(Magpie\Magpie $magpie)
    {
        $charge = $magpie->charges->create([
            'amount' => 25000, // ₱250.00
            'currency' => 'php',
            'source' => $request->source_id,
            'description' => 'Order payment'
        ]);

        return response()->json($charge);
    }
}
```

## Core Features

### Payment Processing

#### Create a Charge

```php
$charge = $magpie->charges->create([
    'amount' => 100000, // ₱1,000.00 in centavos
    'currency' => 'php',
    'source' => 'src_1234567890',
    'description' => 'Payment for premium subscription',
    'metadata' => [
        'order_id' => 'ORDER-123',
        'user_id' => '456'
    ]
]);
```

#### Authorize and Capture

```php
// Create an authorized charge (not captured)
$charge = $magpie->charges->create([
    'amount' => 50000,
    'currency' => 'php',
    'source' => $source_id,
    'capture' => false // Only authorize
]);

// Later, capture the payment
$captured = $magpie->charges->capture($charge['id'], [
    'amount' => 50000 // Can be less than authorized amount
]);
```

#### Process Refunds

```php
// Full refund
$refund = $magpie->charges->refund('ch_1234567890', [
    'reason' => 'requested_by_customer'
]);

// Partial refund
$partialRefund = $magpie->charges->refund('ch_1234567890', [
    'amount' => 25000, // Refund ₱250.00 out of original charge
    'reason' => 'duplicate'
]);
```

### Customer Management

```php
// Create a customer
$customer = $magpie->customers->create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'phone' => '+639171234567',
    'metadata' => [
        'user_id' => '789'
    ]
]);

// Update customer
$updated = $magpie->customers->update($customer['id'], [
    'email' => 'john-new@example.com'
]);

// Retrieve customer
$customer = $magpie->customers->retrieve('cus_1234567890');
```

### Payment Sources

```php
// Credit Card
$cardSource = $magpie->sources->create([
    'type' => 'card',
    'card' => [
        'name' => 'John Doe',
        'number' => '4242424242424242',
        'exp_month' => '12',
        'exp_year' => '2025',
        'cvc' => '123',
        'address_line1' => '123 Main St',
        'address_city' => 'Manila',
        'address_country' => 'PH',
        'address_zip' => '1000'
    ],
    'redirect' => [
        'success' => 'https://example.com/success',
        'fail' => 'https://example.com/fail'
    ]
]);

// Bank Transfer (BPI)
$bankSource = $magpie->sources->create([
    'type' => 'bpi',
    'redirect' => [
        'success' => 'https://example.com/success',
        'fail' => 'https://example.com/fail'
    ],
    'owner' => [
        'name' => 'Account Holder'
    ]
]);

// E-wallet (GCash)
$gcashSource = $magpie->sources->create([
    'type' => 'gcash',
    'redirect' => [
        'success' => 'https://example.com/success',
        'fail' => 'https://example.com/fail'
    ]
]);
```

### Checkout Sessions

Create hosted checkout pages for seamless payment experiences:

```php
// Create a checkout session
$session = $magpie->checkout->sessions->create([
    'line_items' => [
        [
            'name' => 'Premium T-Shirt',
            'amount' => 2500, // ₱25.00
            'quantity' => 2,
            'description' => 'High-quality cotton t-shirt' // Optional
        ]
    ],
    'success_url' => 'https://example.com/success',
    'cancel_url' => 'https://example.com/cancel',
    'customer_email' => 'customer@example.com',
    'expires_at' => time() + 3600 // Expire in 1 hour
]);

// Redirect user to checkout page
header('Location: ' . $session['url']);

// Later, retrieve session to check payment status
$session = $magpie->checkout->sessions->retrieve('cs_1234567890');
echo $session['payment_status']; // 'paid', 'unpaid', 'no_payment_required'

// Capture authorized payment (for sessions with capture: false)
$captured = $magpie->checkout->sessions->capture('cs_1234567890', [
    'amount' => 2000 // Capture ₱20.00 instead of full ₱25.00
]);

// Expire a session manually
$expired = $magpie->checkout->sessions->expire('cs_1234567890');
```

### Payment Requests

Send payment requests to customers via email or SMS:

```php
// Create a payment request
$request = $magpie->paymentRequests->create([
    'currency' => 'php',
    'customer' => 'cus_1234567890', // Required: Customer ID
    'delivery_methods' => ['email', 'sms'], // How to deliver the request
    'line_items' => [
        [
            'name' => 'Monthly Subscription',
            'amount' => 75000, // ₱750.00
            'quantity' => 1
        ]
    ],
    'payment_method_types' => ['card', 'gcash', 'paymaya', 'grabpay'],
    'message' => 'Thank you for your business! Please complete payment within 7 days.',
    'require_auth' => true, // Require 3D Secure for cards
    'branding' => [
        'use_logo' => true,
        'primary_color' => '#0066cc',
        'secondary_color' => '#ffffff'
    ],
    'metadata' => [
        'invoice_id' => 'INV-001',
        'customer_type' => 'premium'
    ]
]);

echo "Payment request created: " . $request['payment_request_url'];

// Retrieve payment request status
$request = $magpie->paymentRequests->retrieve('pr_1234567890');
echo $request['paid'] ? 'Paid' : 'Pending';

// Resend the payment request
$resent = $magpie->paymentRequests->resend('pr_1234567890');

// Void/cancel a payment request
$voided = $magpie->paymentRequests->void('pr_1234567890', [
    'reason' => 'duplicate_request'
]);
```

### Payment Links

Create shareable payment links for social media, email campaigns, or instant invoicing:

```php
// Create a payment link
$link = $magpie->paymentLinks->create([
    'internal_name' => 'Website Design Service',
    'allow_adjustable_quantity' => true,
    'line_items' => [
        [
            'name' => 'Website Design Package',
            'amount' => 100000, // ₱1,000.00
            'quantity' => 1,
            'image' => 'https://example.com/service.jpg',
            'description' => 'Complete website design with 3 revisions'
        ]
    ],
    'metadata' => [
        'service_type' => 'web_design',
        'package' => 'premium'
    ]
]);

echo "Share this link: " . $link['url']; // https://buy.magpie.im/pl_1234567890

// Retrieve payment link details
$link = $magpie->paymentLinks->retrieve('pl_1234567890');
echo "Active: " . ($link['active'] ? 'Yes' : 'No');

// Update payment link
$updated = $magpie->paymentLinks->update('pl_1234567890', [
    'internal_name' => 'Updated Service Name',
    'metadata' => ['campaign' => 'holiday-sale']
]);

// Activate/deactivate payment links
$activated = $magpie->paymentLinks->activate('pl_1234567890');
$deactivated = $magpie->paymentLinks->deactivate('pl_1234567890');
```

### Customer Management Extended

```php
// Retrieve customer by email
$customer = $magpie->customers->retrieveByEmail('john@example.com');

// Attach payment source to customer
$updatedCustomer = $magpie->customers->attachSource('cus_123', 'src_456');

// Detach payment source from customer
$updatedCustomer = $magpie->customers->detachSource('cus_123', 'src_456');
```

## Configuration

### Basic Configuration

```php
use Magpie\Http\Config;
use Magpie\Magpie;

$config = new Config([
    'baseUrl' => 'https://api.magpie.im',
    'apiVersion' => 'v2',
    'timeout' => 30,
    'maxRetries' => 3,
    'debug' => true
]);

$magpie = new Magpie('your_secret_key', $config);
```

### Laravel Configuration

Publish and modify `config/magpie.php`:

```php
return [
    'secret_key' => env('MAGPIE_SECRET_KEY'),
    'base_url' => env('MAGPIE_BASE_URL', 'https://api.magpie.im'),
    'timeout' => (int) env('MAGPIE_TIMEOUT', 30),
    'max_retries' => (int) env('MAGPIE_MAX_RETRIES', 3),
    'debug' => env('MAGPIE_DEBUG', false),
];
```

## Error Handling

The library provides comprehensive error handling with specific exception types:

```php
use Magpie\Exceptions\AuthenticationException;
use Magpie\Exceptions\ValidationException;
use Magpie\Exceptions\RateLimitException;
use Magpie\Exceptions\MagpieException;

try {
    $charge = $magpie->charges->create([
        'amount' => 50000,
        'currency' => 'php',
        'source' => $source_id
    ]);
} catch (AuthenticationException $e) {
    // Invalid API key
    echo "Authentication failed: " . $e->getMessage();
} catch (ValidationException $e) {
    // Invalid parameters
    echo "Validation error: " . $e->getMessage();
    print_r($e->errors); // Field-specific errors
} catch (RateLimitException $e) {
    // Too many requests
    echo "Rate limit exceeded. Try again later.";
} catch (MagpieException $e) {
    // General API error
    echo "API Error: " . $e->getMessage();
    echo "Request ID: " . $e->requestId; // For support
}
```

## Webhook Handling

### Laravel Webhook Controller

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Magpie\Laravel\Facades\Magpie;
use Magpie\Exceptions\MagpieException;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        try {
            $payload = $request->getContent();
            $signature = $request->header('magpie-signature');
            $secret = config('magpie.webhooks.secret');
            
            $event = Magpie::webhooks()->constructEvent(
                $payload,
                $signature,
                $secret
            );
            
            switch ($event['type']) {
                case 'charge.succeeded':
                    $this->handleChargeSucceeded($event['data']['object']);
                    break;
                    
                case 'charge.failed':
                    $this->handleChargeFailed($event['data']['object']);
                    break;
            }
            
            return response('OK', 200);
            
        } catch (MagpieException $e) {
            return response('Webhook error', 400);
        }
    }
    
    private function handleChargeSucceeded($charge)
    {
        // Handle successful payment
        // Update order status, send confirmation email, etc.
    }
}
```

## Advanced Usage

### Custom HTTP Client Configuration

```php
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;

// Custom Guzzle configuration
$stack = HandlerStack::create();
$stack->push(Middleware::log($logger, new MessageFormatter('{req_body} - {res_body}')));

$magpie = new Magpie($secretKey, [
    'timeout' => 60,
    'debug' => true,
    'defaultHeaders' => [
        'X-Custom-Header' => 'value'
    ]
]);
```

### Idempotency

```php
// Use idempotency keys for safe retries
$charge = $magpie->charges->create([
    'amount' => 50000,
    'currency' => 'php',
    'source' => $source_id
], [
    'idempotency_key' => 'unique-operation-id-' . time()
]);
```

## Testing

### Unit Tests

```bash
composer test
```

### Integration Tests

```bash
# Set test API key
export MAGPIE_SECRET_KEY=sk_test_your_test_key

composer test:integration
```

## Development

```bash
# Install dependencies
composer install

# Run tests
composer test

# Run static analysis
composer analyse

# Fix code style
composer fix
```

## Support

- **Documentation**: [https://docs.magpie.im](https://docs.magpie.im)
- **Support**: [support@magpie.im](mailto:support@magpie.im)
- **Issues**: [GitHub Issues](https://github.com/magpieimdev/magpie-php/issues)

## Maintainers

This library is maintained by the Magpie team:

- **Jerick Coneras** - [@donjerick](https://github.com/donjerick) - Lead Maintainer
- **Magpie Team** - [support@magpie.im](mailto:support@magpie.im)

## Security

If you discover any security vulnerabilities, please email [support@magpie.im](mailto:support@magpie.im) instead of using the issue tracker.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for a list of changes.
