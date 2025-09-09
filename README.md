# Magpie PHP Library

The official Magpie PHP library for seamless integration with Magpie's payment processing APIs. Built for modern PHP applications and Laravel, with comprehensive support for all Magpie API features.

[![Latest Version](https://img.shields.io/packagist/v/magpieim/magpie-php.svg?style=flat-square)](https://packagist.org/packages/magpieim/magpie-php)
[![PHP Version](https://img.shields.io/packagist/php-v/magpieim/magpie-php.svg?style=flat-square)](https://packagist.org/packages/magpieim/magpie-php)
[![Total Downloads](https://img.shields.io/packagist/dt/magpieim/magpie-php.svg?style=flat-square)](https://packagist.org/packages/magpieim/magpie-php)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg?style=flat-square)](https://opensource.org/licenses/MIT)

## Features

- **💳 Complete Payment Processing**: Cards, bank transfers, e-wallets (GCash, Maya, etc.)
- **🔄 Hybrid API Design**: Choose between simple arrays or type-safe DTOs
- **🛡️ Full Type Safety**: IDE autocompletion, compile-time validation, and runtime checks
- **🔒 Secure by Design**: Built-in authentication, SSL verification, and secure error handling
- **⚡ Laravel Integration**: Service provider, facades, and configuration publishing
- **🔄 Automatic Retries**: Exponential backoff with jitter for transient failures
- **📝 Comprehensive Logging**: Debug mode with request/response logging
- **🌐 Modern HTTP Client**: Built on Guzzle with middleware support
- **⬅️ Backward Compatible**: Existing array-based code continues to work unchanged

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

The Magpie PHP SDK supports two usage patterns: **array-based** (simple and backward compatible) and **DTO-based** (type-safe with IDE support).

### Array-Based Usage (Simple)

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

// Create a charge
$charge = $magpie->charges->create([
    'amount' => 50000, // ₱500.00 in centavos
    'currency' => 'php',
    'source' => 'src_1234567890',
    'description' => 'Payment for Order #1234'
]);

echo "Charge created: " . $charge['id'] . "\n";
```

### DTO-Based Usage (Type-Safe)

```php
<?php

require_once 'vendor/autoload.php';

use Magpie\Magpie;
use Magpie\DTOs\Requests\Customers\CreateCustomerRequest;
use Magpie\DTOs\Requests\Charges\CreateChargeRequest;

// Initialize the client  
$magpie = new Magpie('sk_test_your_secret_key_here');

// Create a customer with full IDE support
$customerRequest = new CreateCustomerRequest(
    email: 'john@example.com',
    description: 'Premium customer',
    name: 'John Doe',
    mobile_number: '+639171234567'
);

$customer = $magpie->customers->create($customerRequest);

// Create a charge with type safety
$chargeRequest = new CreateChargeRequest(
    amount: 50000, // ₱500.00 in centavos
    currency: 'php',
    source: 'src_1234567890',
    description: 'Payment for Order #1234',
    capture: true
);

$charge = $magpie->charges->create($chargeRequest);

echo "Charge created: " . $charge->id . "\n"; // Type-safe property access
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

## Type Safety Benefits

When using DTOs, you get:
- **IDE autocompletion** - See available properties and methods
- **Type validation** - Catch errors before runtime  
- **Better refactoring** - Safe code changes across your project

```php
// Arrays: No IDE help, runtime errors possible
$charge = $magpie->charges->create(['amount' => 50000, 'currency' => 'php']);
echo $charge['id']; // ❌ Might break if API changes

// DTOs: Full IDE support, compile-time safety
$request = new CreateChargeRequest(amount: 50000, currency: 'php');
$charge = $magpie->charges->create($request);
echo $charge->id; // ✅ IDE knows this exists
```

## Core Features

### Payment Processing

#### Create a Charge

**Array-based approach:**

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

**DTO-based approach:**

```php
use Magpie\DTOs\Requests\Charges\CreateChargeRequest;

$chargeRequest = new CreateChargeRequest(
    amount: 100000, // ₱1,000.00 in centavos
    currency: 'php',
    source: 'src_1234567890',
    description: 'Payment for premium subscription',
    metadata: [
        'order_id' => 'ORDER-123',
        'user_id' => '456'
    ]
);

$charge = $magpie->charges->create($chargeRequest);
```

### Checkout Sessions

Create hosted checkout pages for seamless payment experiences:

**Array-based approach:**

```php
// Create a checkout session
$session = $magpie->checkout->sessions->create([
    'line_items' => [
        [
            'name' => 'Premium T-Shirt',
            'amount' => 2500, // ₱25.00
            'quantity' => 2,
            'description' => 'High-quality cotton t-shirt'
        ]
    ],
    'success_url' => 'https://example.com/success',
    'cancel_url' => 'https://example.com/cancel',
    'customer_email' => 'customer@example.com'
]);

// Redirect user to checkout page
header('Location: ' . $session['payment_url']);
```

**DTO-based approach (with type safety):**

```php
use Magpie\DTOs\Requests\Checkout\CreateCheckoutSessionRequest;
use Magpie\DTOs\ValueObjects\LineItem;

$sessionRequest = new CreateCheckoutSessionRequest(
    line_items: [
        new LineItem(
            amount: 2500, // ₱25.00
            quantity: 2,
            description: 'Premium T-Shirt'
        )
    ],
    success_url: 'https://example.com/success',
    cancel_url: 'https://example.com/cancel',
    customer_email: 'customer@example.com'
);

$session = $magpie->checkout->sessions->create($sessionRequest);

// Redirect user to checkout page
header('Location: ' . $session->payment_url);
```

## Configuration

### Basic Configuration

The Magpie client accepts configuration options:

```php
use Magpie\Magpie;

// Default configuration
$magpie = new Magpie('sk_test_your_secret_key');

// With custom configuration
$magpie = new Magpie('sk_test_your_secret_key', [
    'timeout' => 30000,        // 30 seconds
    'maxNetworkRetries' => 3,  // Maximum retry attempts  
    'debug' => true            // Enable debug logging
]);
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
