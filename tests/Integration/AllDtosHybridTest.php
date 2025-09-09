<?php

declare(strict_types=1);

namespace Magpie\Tests\Integration;

use Magpie\DTOs\Responses\Charge;
use Magpie\DTOs\Responses\CheckoutSession;
use Magpie\DTOs\Responses\Customer;
use Magpie\DTOs\Responses\Organization;
use Magpie\DTOs\Responses\PaymentLink;
use Magpie\DTOs\Responses\PaymentRequest;
use Magpie\DTOs\Responses\Source;
use Magpie\DTOs\Responses\WebhookEvent;
use Magpie\Enums\SourceType;
use PHPUnit\Framework\TestCase;

/**
 * Comprehensive test to verify all DTOs support hybrid API access.
 * 
 * This test ensures that every DTO in the SDK can be accessed both as
 * objects (new way) and as arrays (backward compatible way).
 */
class AllDtosHybridTest extends TestCase
{
    public function testSourceHybridAccess(): void
    {
        $data = [
            'id' => 'src_123',
            'object' => 'source',
            'type' => 'card',
            'redirect' => ['success' => 'https://example.com/success', 'fail' => 'https://example.com/fail'],
            'vaulted' => true,
            'used' => false,
            'livemode' => false,
            'created_at' => '2024-01-01T00:00:00Z',
            'updated_at' => '2024-01-01T00:00:00Z',
            'metadata' => []
        ];

        $source = Source::fromArray($data);

        // Object access
        $this->assertSame('src_123', $source->id);
        $this->assertSame(SourceType::CARD, $source->type);
        
        // Array access
        $this->assertSame('src_123', $source['id']);
        $this->assertSame('card', $source['type']);
    }

    public function testCustomerHybridAccess(): void
    {
        $data = [
            'id' => 'cus_123',
            'object' => 'customer',
            'email' => 'test@example.com',
            'description' => 'Test customer',
            'mobile_number' => '+639151234567',
            'livemode' => false,
            'created_at' => '2024-01-01T00:00:00Z',
            'updated_at' => '2024-01-01T00:00:00Z',
            'metadata' => [],
            'name' => 'Test User',
            'sources' => []
        ];

        $customer = Customer::fromArray($data);

        // Object access
        $this->assertSame('cus_123', $customer->id);
        $this->assertSame('test@example.com', $customer->email);
        
        // Array access
        $this->assertSame('cus_123', $customer['id']);
        $this->assertSame('test@example.com', $customer['email']);
    }

    public function testChargeHybridAccess(): void
    {
        $data = [
            'id' => 'ch_123',
            'object' => 'charge',
            'amount' => 10000,
            'amount_refunded' => 0,
            'authorized' => true,
            'captured' => true,
            'currency' => 'php',
            'statement_descriptor' => 'TEST',
            'description' => 'Test charge',
            'source' => [
                'id' => 'src_123',
                'object' => 'source',
                'type' => 'card',
                'redirect' => ['success' => 'https://example.com/success', 'fail' => 'https://example.com/fail'],
                'vaulted' => true,
                'used' => false,
                'livemode' => false,
                'created_at' => '2024-01-01T00:00:00Z',
                'updated_at' => '2024-01-01T00:00:00Z',
                'metadata' => []
            ],
            'require_auth' => false,
            'owner' => null,
            'action' => null,
            'refunds' => [],
            'status' => 'succeeded',
            'livemode' => false,
            'created_at' => '2024-01-01T00:00:00Z',
            'updated_at' => '2024-01-01T00:00:00Z',
            'metadata' => [],
            'failure_data' => null
        ];

        $charge = Charge::fromArray($data);

        // Object access
        $this->assertSame('ch_123', $charge->id);
        $this->assertSame(10000, $charge->amount);
        
        // Array access
        $this->assertSame('ch_123', $charge['id']);
        $this->assertSame(10000, $charge['amount']);
    }

    public function testCheckoutSessionHybridAccess(): void
    {
        $data = [
            'id' => 'cs_test_123',
            'object' => 'checkout.session',
            'amount_subtotal' => 9500,
            'amount_total' => 10000,
            'branding' => null,
            'billing_address_collection' => 'auto',
            'cancel_url' => 'https://example.com/cancel',
            'created_at' => '2024-01-01T00:00:00Z',
            'expires_at' => '2024-01-02T00:00:00Z',
            'currency' => 'php',
            'customer_name_collection' => true,
            'last_updated' => '2024-01-01T00:00:00Z',
            'line_items' => [],
            'livemode' => false,
            'locale' => 'en',
            'merchant' => [
                'name' => 'Test Merchant',
                'support_email' => 'support@example.com',
                'support_phone' => '+639151234567'
            ],
            'metadata' => [],
            'mode' => 'payment',
            'payment_method_types' => ['card'],
            'payment_status' => 'unpaid',
            'payment_url' => 'https://checkout.example.com/cs_test_123',
            'phone_number_collection' => false,
            'require_auth' => false,
            'submit_type' => 'pay',
            'success_url' => 'https://example.com/success'
        ];

        $session = CheckoutSession::fromArray($data);

        // Object access
        $this->assertSame('cs_test_123', $session->id);
        $this->assertSame(10000, $session->amount_total);
        $this->assertSame('Test Merchant', $session->merchant->name);
        
        // Array access
        $this->assertSame('cs_test_123', $session['id']);
        $this->assertSame(10000, $session['amount_total']);
        $this->assertSame('Test Merchant', $session['merchant']['name']);
    }

    public function testOrganizationHybridAccess(): void
    {
        $data = [
            'object' => 'organization',
            'id' => 'org_test_123',
            'title' => 'Test Organization',
            'account_name' => 'Test Account',
            'statement_descriptor' => 'TEST*COMPANY',
            'pk_test_key' => 'pk_test_123456',
            'sk_test_key' => 'sk_test_123456',
            'pk_live_key' => 'pk_live_123456',
            'sk_live_key' => 'sk_live_123456',
            'branding' => [],
            'status' => 'active',
            'created_at' => '2024-01-01T00:00:00Z',
            'updated_at' => '2024-01-01T00:00:00Z',
            'payment_method_settings' => [],
            'rates' => [],
            'payout_settings' => [],
            'metadata' => [],
            'business_address' => '123 Test St, Test City'
        ];

        $organization = Organization::fromArray($data);

        // Object access
        $this->assertSame('org_test_123', $organization->id);
        $this->assertSame('Test Organization', $organization->title);
        $this->assertSame('pk_test_123456', $organization->pk_test_key);
        
        // Array access
        $this->assertSame('org_test_123', $organization['id']);
        $this->assertSame('Test Organization', $organization['title']);
        $this->assertSame('pk_test_123456', $organization['pk_test_key']);
    }

    public function testPaymentLinkHybridAccess(): void
    {
        $data = [
            'id' => 'pl_test_123',
            'object' => 'payment_link',
            'active' => true,
            'allow_adjustable_quantity' => false,
            'branding' => null,
            'created' => 1704067200, // 2024-01-01 timestamp
            'currency' => 'php',
            'internal_name' => 'Test Payment Link',
            'line_items' => [],
            'livemode' => false,
            'metadata' => [],
            'payment_method_types' => ['card'],
            'require_auth' => false,
            'updated' => 1704067200,
            'url' => 'https://buy.magpie.im/pl_test_123',
            'description' => 'Test payment link description'
        ];

        $paymentLink = PaymentLink::fromArray($data);

        // Object access
        $this->assertSame('pl_test_123', $paymentLink->id);
        $this->assertSame('Test Payment Link', $paymentLink->internal_name);
        $this->assertTrue($paymentLink->active);
        
        // Array access
        $this->assertSame('pl_test_123', $paymentLink['id']);
        $this->assertSame('Test Payment Link', $paymentLink['internal_name']);
        $this->assertTrue($paymentLink['active']);
    }

    public function testPaymentRequestHybridAccess(): void
    {
        $data = [
            'id' => 'pr_test_123',
            'object' => 'payment_request',
            'account_name' => 'Test Account',
            'branding' => null,
            'created' => 1704067200,
            'currency' => 'php',
            'customer' => 'cus_test_123',
            'customer_email' => 'customer@example.com',
            'customer_name' => 'Test Customer',
            'delivery_methods' => ['email', 'sms'],
            'delivered' => [
                'email' => true,
                'sms' => false
            ],
            'line_items' => [],
            'livemode' => false,
            'metadata' => [],
            'number' => 'PR-001',
            'paid' => false,
            'payment_method_types' => ['card'],
            'payment_request_url' => 'https://request.magpie.im/pr_test_123',
            'require_auth' => false,
            'subtotal' => 5000,
            'total' => 5000,
            'updated' => 1704067200,
            'voided' => false
        ];

        $paymentRequest = PaymentRequest::fromArray($data);

        // Object access
        $this->assertSame('pr_test_123', $paymentRequest->id);
        $this->assertSame('Test Account', $paymentRequest->account_name);
        $this->assertSame(5000, $paymentRequest->total);
        $this->assertTrue($paymentRequest->delivered->email);
        
        // Array access
        $this->assertSame('pr_test_123', $paymentRequest['id']);
        $this->assertSame('Test Account', $paymentRequest['account_name']);
        $this->assertSame(5000, $paymentRequest['total']);
        $this->assertTrue($paymentRequest['delivered']['email']);
    }

    public function testWebhookEventHybridAccess(): void
    {
        $data = [
            'id' => 'evt_test_123',
            'type' => 'charge.succeeded',
            'data' => [
                'object' => [
                    'id' => 'ch_test_123',
                    'amount' => 10000
                ]
            ],
            'created' => 1704067200,
            'livemode' => false,
            'api_version' => '2024-01-01',
            'pending_webhooks' => 1,
            'request' => ['id' => 'req_test_123']
        ];

        $webhookEvent = WebhookEvent::fromArray($data);

        // Object access
        $this->assertSame('evt_test_123', $webhookEvent->id);
        $this->assertSame('charge.succeeded', $webhookEvent->type->value);
        $this->assertSame('ch_test_123', $webhookEvent->data['object']['id']);
        $this->assertSame(1704067200, $webhookEvent->created);
        
        // Array access
        $this->assertSame('evt_test_123', $webhookEvent['id']);
        $this->assertSame('charge.succeeded', $webhookEvent['type']);
        $this->assertSame('ch_test_123', $webhookEvent['data']['object']['id']);
        $this->assertSame(1704067200, $webhookEvent['created']);
    }

    public function testHybridArrayAccessMethods(): void
    {
        $data = [
            'id' => 'test_123',
            'object' => 'source',
            'type' => 'card',
            'redirect' => ['success' => 'https://example.com/success', 'fail' => 'https://example.com/fail'],
            'vaulted' => true,
            'used' => false,
            'livemode' => false,
            'created_at' => '2024-01-01T00:00:00Z',
            'updated_at' => '2024-01-01T00:00:00Z',
            'metadata' => ['key' => 'value']
        ];

        $dto = Source::fromArray($data);

        // Test isset
        $this->assertTrue(isset($dto['id']));
        $this->assertFalse(isset($dto['nonexistent']));

        // Test array modification
        $dto['custom'] = 'test_value';
        $this->assertSame('test_value', $dto['custom']);

        // Test unset
        unset($dto['custom']);
        $this->assertFalse(isset($dto['custom']));
        $this->assertNull($dto['custom']);
    }
}