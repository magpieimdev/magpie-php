# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.0] - 2025-09-09

### Added

- 🎯 Hybrid API design: use either plain arrays or typed DTOs across the SDK
- 🧩 ArrayAccess on responses: BaseResponse implements ArrayAccess for ergonomic array-like access
- 📦 **Comprehensive value objects**: 19+ new value objects including:
  - Payment structure: `LineItem`, `PaymentLinkItem`, `Refund`
  - Address handling: `Address`, `Billing`, `Shipping`, `CheckoutSessionAddress`, `ShippingAddressCollection`
  - Source details: `SourceCard`, `SourceBankAccount`, `SourceOwner`, `SourceRedirect`
  - Branding & UI: `BrandingOptions`, `CheckoutSessionMerchant`
  - Payment flow: `ChargeAction`, `ChargeFailure`, `ChargeProviderResponse`, `PaymentRequestDelivered`
- 🏗️ **Complex nested type support**: Automatic handling of deeply nested object structures with proper PHP typing
- 🎨 Smart serialization: automatic conversion between arrays and typed objects
- 🔢 Enhanced enums: Additional enum cases added (e.g., `ChargeStatus` now includes `VOIDED`, `REFUNDED`)

### Changed

- Contracts now accept `Request|array` parameters for all service interfaces
- Resources return and accept mixed types (arrays or DTOs) with improved type handling
- Customer resource: smart transformation between `name` and `metadata.name`
- DTOs enriched with stricter typing and value objects (replacing generic arrays)
- BaseRequest/BaseResponse improved with more robust (de)serialization helpers

### Tests

- **Unit tests enhanced**: Comprehensive coverage for hybrid array/DTO flows and type validations across all resources
- **Integration tests added**: 
  - `AllDtosHybridTest`: Validates ArrayAccess compatibility for all DTO response types (Source, Customer, Charge, CheckoutSession, Organization, PaymentLink, PaymentRequest, WebhookEvent)
  - `HybridApiTest`: Live API validation testing core resource operations with both array and DTO approaches
  - Ensures backward compatibility while validating new hybrid functionality

### Technical Implementation

- **ArrayAccess Interface**: `BaseResponse` now implements `ArrayAccess`, allowing `$response['key']` syntax alongside `$response->key`
- **Mixed Return Types**: Service contracts and resources updated to support `mixed` return types for flexibility
- **Type-Safe Serialization**: Enhanced `fromArray()` methods with robust type conversion and validation
- **Nested Object Mapping**: Automatic conversion of nested arrays to appropriate value objects
- **Memory Efficient**: No duplication of data between array and object representations

### Compatibility

- **Fully Backward Compatible**: Existing array-based code continues to work without any changes
- **Zero Breaking Changes**: All enhancements are additive and opt-in via DTO usage
- **Progressive Enhancement**: Developers can gradually adopt DTOs at their own pace

### Documentation

- README updated with hybrid API examples and DTO adoption guidance
- Comprehensive value object documentation and usage examples

## [1.0.0] - 2025-09-05

### Added

#### 🚀 Complete SDK Implementation

- **100% Feature Parity** with Node.js Magpie SDK
- **Full PHP 8.1+** implementation with strict type declarations
- **Laravel Integration** with service provider, facades, and configuration publishing
- **Comprehensive Documentation** with extensive usage examples

#### 💳 Payment Processing

- **ChargesResource**: Create, retrieve, capture, void, refund, and verify charges
- **Authorization & Capture**: Two-step payment processing support
- **Refunds**: Full and partial refund capabilities with reason tracking
- **Payment Verification**: 3D Secure and bank payment verification

#### 👥 Customer Management

- **CustomersResource**: Complete CRUD operations
- **Email Lookup**: Retrieve customers by email address
- **Source Management**: Attach/detach payment sources to customers
- **Extended Methods**: `retrieveByEmail()`, `attachSource()`, `detachSource()`

#### 💰 Payment Sources

- **SourcesResource**: Support for all payment methods
- **Credit/Debit Cards**: Full card processing with address validation
- **Bank Transfers**: BPI, BDO, and other Philippine banks
- **E-wallets**: GCash, PayMaya, GrabPay integration
- **QR Codes**: QR PH and other QR payment methods

#### 🛒 Checkout & Payment Pages

- **CheckoutSessionsResource**: Hosted checkout page management
- **Session Lifecycle**: Create, retrieve, capture, and expire operations
- **Custom Branding**: Logo, colors, and messaging customization
- **Multi-base URL Support**: Uses `https://new.pay.magpie.im` for checkout

#### 📧 Payment Requests

- **PaymentRequestsResource**: Email and SMS payment delivery
- **Multi-channel Delivery**: Support for email, SMS, or both
- **Advanced Configuration**: Custom branding, payment methods, messaging
- **Request Management**: Create, retrieve, resend, and void operations
- **API Endpoint**: Uses `https://request.magpie.im/api/v1`

#### 🔗 Payment Links

- **PaymentLinksResource**: Shareable payment links
- **Link Customization**: Adjustable quantities, product images, descriptions
- **Link Management**: Create, update, activate, and deactivate
- **Social Media Ready**: Optimized for sharing across platforms
- **API Endpoint**: Uses `https://buy.magpie.im/api/v1`

#### 🔐 Webhooks & Security

- **WebhooksResource**: Complete webhook verification system
- **Signature Verification**: HMAC-SHA256 with timing-safe comparison
- **Timestamp Validation**: Configurable tolerance for replay protection
- **Event Construction**: Automatic parsing of webhook payloads
- **Testing Utilities**: Generate test signatures for development
- **Methods**: `verifySignature()`, `verifySignatureWithTimestamp()`, `constructEvent()`

#### 🌐 HTTP & Networking

- **Guzzle-based Client**: Advanced HTTP client with middleware support
- **Automatic Retries**: Exponential backoff with jitter
- **Request Logging**: Debug mode with detailed tracing
- **SSL Security**: Certificate verification and secure communication
- **Idempotency**: Built-in support via `X-Idempotency-Key` header
- **Multiple Base URLs**: Resource-specific API endpoints

#### ⚠️ Error Handling

- **Exception Hierarchy**: Specific exceptions for different error types
  - `ConfigurationException`: SDK configuration errors
  - `AuthenticationException`: API key and auth errors
  - `ValidationException`: Parameter validation errors
  - `RateLimitException`: API rate limiting
  - `NetworkException`: Connectivity issues
  - `NotFoundException`: Resource not found
  - `PermissionException`: Authorization errors
- **Rich Error Data**: Request IDs, status codes, detailed messages

#### 🏗️ Laravel Integration

- **Service Provider**: `MagpieServiceProvider` with auto-registration
- **Facade**: Clean Laravel-style API access
- **Configuration Publishing**: Environment-based setup
- **Dependency Injection**: Full IoC container support

#### 🛠️ Developer Experience

- **Type Safety**: Complete PHP 8.1+ type declarations
- **IDE Support**: Full autocomplete and intellisense
- **Comprehensive Examples**: Real-world usage scenarios
- **Debug Tools**: Request/response logging capabilities
- **Unit Tests**: Extensive test suite with mocking

#### 📚 Documentation

- **Complete README**: Installation, usage, and examples
- **API Reference**: All methods documented with examples
- **Laravel Guide**: Framework-specific integration instructions
- **Error Handling Guide**: Exception handling best practices
- **Webhook Setup**: Complete webhook implementation guide

### Technical Specifications

#### Requirements

- **PHP**: 8.1 or higher
- **Laravel**: 10+ (for integration features)
- **HTTP Client**: Guzzle 7.0+

#### Dependencies

- `guzzlehttp/guzzle`: ^7.0 (HTTP client)
- `psr/http-client`: ^1.0 (PSR-18 interface)
- `psr/http-message`: ^1.0|^2.0 (PSR-7 interface)
- `psr/log`: ^1.0|^2.0|^3.0 (PSR-3 logging)

#### Development Dependencies

- `phpunit/phpunit`: ^10.0 (Testing framework)
- `mockery/mockery`: ^1.5 (Mocking library)
- `phpstan/phpstan`: ^1.0 (Static analysis)
- `php-cs-fixer/shim`: ^3.0 (Code style)
- `orchestra/testbench`: ^8.0 (Laravel testing)

### Installation

```bash
composer require magpieim/magpie-php
```

### Basic Usage

```php
use Magpie\Magpie;

$magpie = new Magpie('sk_test_your_key_here');

$charge = $magpie->charges->create([
    'amount' => 50000, // ₱500.00
    'currency' => 'php',
    'source' => $source_id,
    'description' => 'Test payment'
]);
```

### Laravel Usage

```php
use Magpie\Laravel\Facades\Magpie;

$customer = Magpie::customers()->create([
    'name' => 'John Doe',
    'email' => 'john@example.com'
]);
```

### Security Features

- Webhook signature verification with timing-safe comparison
- API key validation on initialization
- SSL certificate verification
- Request idempotency support
- Secure error handling (no sensitive data in logs)

### Performance Features

- Connection pooling and reuse
- Automatic retry with exponential backoff
- Efficient HTTP middleware stack
- Minimal memory footprint
- Optimized for high-throughput applications

