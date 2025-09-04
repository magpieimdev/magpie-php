# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Initial release of the Magpie PHP SDK
- Complete payment processing functionality (charges, customers, sources)
- Laravel integration with service provider and facade
- Comprehensive error handling with specific exception types
- Automatic retry mechanism with exponential backoff
- Debug logging capabilities
- Webhook handling utilities
- Full PHP 8.1+ type declarations
- PHPUnit test suite with coverage reporting
- PHPStan static analysis configuration
- GitHub Actions CI/CD pipeline

### Core Features
- **HTTP Client**: Guzzle-based client with middleware support
- **Resource Classes**: ChargesResource, CustomersResource, SourcesResource, etc.
- **Exception Handling**: MagpieException with specific subtypes
- **Laravel Support**: ServiceProvider, Facade, and configuration publishing
- **Configuration**: Flexible configuration with environment variable support
- **Testing**: Comprehensive test suite with mocking capabilities

### Supported Payment Methods
- Credit/Debit Cards
- Bank Transfers (BPI)
- E-wallets (GCash, Maya)
- QR PH payments

## [1.0.0] - TBD

### Added
- Initial stable release
