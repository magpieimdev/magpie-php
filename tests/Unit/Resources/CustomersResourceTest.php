<?php

declare(strict_types=1);

namespace Magpie\Tests\Unit\Resources;

use Magpie\Exceptions\MagpieException;
use Magpie\Http\Client;
use Magpie\Resources\CustomersResource;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magpie\Resources\CustomersResource
 */
class CustomersResourceTest extends TestCase
{
    protected function tearDown(): void
    {
        \Mockery::close();
    }

    /**
     * Create complete customer mock data with all required fields for the Customer DTO.
     */
    public static function createCompleteCustomerData(array $overrides = []): array
    {
        $defaults = [
            'id' => 'cus_test_123',
            'object' => 'customer',
            'email' => 'john@example.com',
            'description' => 'Test customer',
            'mobile_number' => '+639151234567',
            'livemode' => false,
            'created_at' => '2022-01-01T00:00:00Z',
            'updated_at' => '2022-01-01T00:00:00Z',
            'metadata' => [],
            'name' => 'John Doe',
            'sources' => [],
        ];

        return array_merge($defaults, $overrides);
    }

    public function testCreate(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new CustomersResource($client);

        $params = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+639151234567',
            'description' => 'Premium customer',
            'metadata' => [
                'user_id' => '12345',
                'plan' => 'premium',
            ],
        ];

        // Expected payload after transformation (name moved to metadata)
        $expectedPayload = [
            'email' => 'john@example.com',
            'phone' => '+639151234567',
            'description' => 'Premium customer',
            'metadata' => [
                'user_id' => '12345',
                'plan' => 'premium',
                'name' => 'John Doe',
            ],
        ];

        // API response with complete data for hybrid API
        $apiResponse = self::createCompleteCustomerData([
            'email' => 'john@example.com',
            'mobile_number' => '+639151234567',
            'description' => 'Premium customer',
            'metadata' => [
                'user_id' => '12345',
                'plan' => 'premium',
                'name' => 'John Doe',
            ],
            'name' => 'John Doe',
        ]);

        $client->shouldReceive('post')
            ->once()
            ->with('customers', $expectedPayload, [])
            ->andReturn($apiResponse);

        $result = $resource->create($params);

        // Test array access (backward compatibility)
        $this->assertSame('cus_test_123', $result['id']);
        $this->assertSame('customer', $result['object']);
        $this->assertSame('John Doe', $result['name']);
        $this->assertSame('john@example.com', $result['email']);
        $this->assertSame('+639151234567', $result['mobile_number']);
        $this->assertSame('Premium customer', $result['description']);
        
        // Test object access (new hybrid API)
        $this->assertSame('cus_test_123', $result->id);
        $this->assertSame('customer', $result->object);
        $this->assertSame('John Doe', $result->name);
        $this->assertSame('john@example.com', $result->email);
        $this->assertSame('+639151234567', $result->mobile_number);
        $this->assertSame('Premium customer', $result->description);
        
        $this->assertSame('12345', $result['metadata']['user_id']);
        $this->assertSame('premium', $result['metadata']['plan']);
    }

    public function testRetrieve(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new CustomersResource($client);

        $customerId = 'cus_test_123';
        $expectedResponse = self::createCompleteCustomerData([
            'id' => $customerId,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'sources' => [
                [
                    'id' => 'src_456',
                    'type' => 'gcash',
                ],
            ],
        ]);

        $client->shouldReceive('get')
            ->once()
            ->with("customers/{$customerId}", null, [])
            ->andReturn($expectedResponse);

        $result = $resource->retrieve($customerId);

        // Test array access (backward compatibility)
        $this->assertSame($customerId, $result['id']);
        $this->assertSame('John Doe', $result['name']);
        $this->assertSame('john@example.com', $result['email']);
        $this->assertSame('customer', $result['object']);
        
        // Test object access (new hybrid API)
        $this->assertSame($customerId, $result->id);
        $this->assertSame('John Doe', $result->name);
        $this->assertSame('john@example.com', $result->email);
        $this->assertSame('customer', $result->object);
        $this->assertSame('src_456', $result['sources'][0]['id']);
    }

    public function testUpdate(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new CustomersResource($client);

        $customerId = 'cus_test_123';
        $params = [
            'name' => 'John Smith',
            'phone' => '+639157654321',
            'metadata' => [
                'plan' => 'enterprise',
            ],
        ];

        // Expected payload after transformation (name moved to metadata)
        $expectedPayload = [
            'phone' => '+639157654321',
            'metadata' => [
                'plan' => 'enterprise',
                'name' => 'John Smith',
            ],
        ];

        // API response with complete data for hybrid API
        $apiResponse = self::createCompleteCustomerData([
            'id' => $customerId,
            'email' => 'john@example.com',
            'mobile_number' => '+639157654321',
            'name' => 'John Smith',
            'metadata' => [
                'user_id' => '12345',
                'plan' => 'enterprise',
                'name' => 'John Smith',
            ],
        ]);

        $client->shouldReceive('put')
            ->once()
            ->with("customers/{$customerId}", $expectedPayload, [])
            ->andReturn($apiResponse);

        $result = $resource->update($customerId, $params);

        // Test array access (backward compatibility)
        $this->assertSame($customerId, $result['id']);
        $this->assertSame('John Smith', $result['name']);
        $this->assertSame('+639157654321', $result['mobile_number']);
        $this->assertSame('enterprise', $result['metadata']['plan']);
        
        // Test object access (new hybrid API)
        $this->assertSame($customerId, $result->id);
        $this->assertSame('John Smith', $result->name);
        $this->assertSame('+639157654321', $result->mobile_number);
        $this->assertSame('customer', $result->object);
    }

    public function testRetrieveByEmail(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new CustomersResource($client);

        $email = 'john@example.com';
        
        // API response with complete data for hybrid API
        $apiResponse = self::createCompleteCustomerData([
            'id' => 'cus_test_123',
            'email' => $email,
            'mobile_number' => '+639151234567',
            'name' => 'John Doe',
            'metadata' => [
                'name' => 'John Doe',
            ],
        ]);

        $client->shouldReceive('request')
            ->once()
            ->with('GET', 'customers/by_email/' . $email, null, [])
            ->andReturn($apiResponse);

        $result = $resource->retrieveByEmail($email);

        // Test array access (backward compatibility)
        $this->assertSame('cus_test_123', $result['id']);
        $this->assertSame($email, $result['email']);
        $this->assertSame('John Doe', $result['name']);
        $this->assertSame('customer', $result['object']);
        
        // Test object access (new hybrid API)
        $this->assertSame('cus_test_123', $result->id);
        $this->assertSame($email, $result->email);
        $this->assertSame('John Doe', $result->name);
        $this->assertSame('customer', $result->object);
    }

    public function testAttachSource(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new CustomersResource($client);

        $customerId = 'cus_test_123';
        $sourceId = 'src_test_456';

        $expectedResponse = self::createCompleteCustomerData([
            'id' => $customerId,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'sources' => [
                [
                    'id' => $sourceId,
                    'type' => 'gcash',
                    'status' => 'verified',
                ],
            ],
        ]);

        $client->shouldReceive('request')
            ->once()
            ->with('POST', "customers/{$customerId}/sources", ['source' => $sourceId], [])
            ->andReturn($expectedResponse);

        $result = $resource->attachSource($customerId, $sourceId);

        // Test array access (backward compatibility)
        $this->assertSame($customerId, $result['id']);
        $this->assertSame($sourceId, $result['sources'][0]['id']);
        $this->assertSame('customer', $result['object']);
        
        // Test object access (new hybrid API)
        $this->assertSame($customerId, $result->id);
        $this->assertSame('customer', $result->object);
        $this->assertSame('John Doe', $result->name);
        $this->assertSame('john@example.com', $result->email);
    }

    public function testDetachSource(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new CustomersResource($client);

        $customerId = 'cus_test_123';
        $sourceId = 'src_test_456';

        $expectedResponse = self::createCompleteCustomerData([
            'id' => $customerId,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'sources' => [],
        ]);

        $client->shouldReceive('request')
            ->once()
            ->with('DELETE', "customers/{$customerId}/sources/{$sourceId}", null, [])
            ->andReturn($expectedResponse);

        $result = $resource->detachSource($customerId, $sourceId);

        // Test array access (backward compatibility)
        $this->assertSame($customerId, $result['id']);
        $this->assertEmpty($result['sources']);
        $this->assertSame('customer', $result['object']);
        
        // Test object access (new hybrid API)
        $this->assertSame($customerId, $result->id);
        $this->assertSame('customer', $result->object);
        $this->assertSame('John Doe', $result->name);
        $this->assertSame('john@example.com', $result->email);
    }

    public function testCreateWithMinimalParams(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new CustomersResource($client);

        $params = [
            'email' => 'minimal@example.com',
        ];

        $expectedResponse = self::createCompleteCustomerData([
            'id' => 'cus_test_456',
            'email' => 'minimal@example.com',
            'name' => null,
            'mobile_number' => null,
        ]);

        $client->shouldReceive('post')
            ->once()
            ->with('customers', $params, [])
            ->andReturn($expectedResponse);

        $result = $resource->create($params);

        // Test array access (backward compatibility)
        $this->assertSame('cus_test_456', $result['id']);
        $this->assertSame('minimal@example.com', $result['email']);
        $this->assertSame('customer', $result['object']);
        
        // Test object access (new hybrid API)
        $this->assertSame('cus_test_456', $result->id);
        $this->assertSame('minimal@example.com', $result->email);
        $this->assertSame('customer', $result->object);
        $this->assertNull($result->name);
    }

    public function testCreateWithOptions(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new CustomersResource($client);

        $params = [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ];

        // Expected payload after transformation (name moved to metadata)
        $expectedPayload = [
            'email' => 'jane@example.com',
            'metadata' => [
                'name' => 'Jane Doe',
            ],
        ];

        $options = ['idempotency_key' => 'customer_create_123'];

        // API response with complete data for hybrid API
        $apiResponse = self::createCompleteCustomerData([
            'id' => 'cus_test_789',
            'email' => 'jane@example.com',
            'name' => 'Jane Doe',
            'metadata' => [
                'name' => 'Jane Doe',
            ],
        ]);

        $client->shouldReceive('post')
            ->once()
            ->with('customers', $expectedPayload, $options)
            ->andReturn($apiResponse);

        $result = $resource->create($params, $options);

        // Test array access (backward compatibility)
        $this->assertSame('cus_test_789', $result['id']);
        $this->assertSame('jane@example.com', $result['email']);
        $this->assertSame('customer', $result['object']);
        
        // Test object access (new hybrid API)
        $this->assertSame('cus_test_789', $result->id);
        $this->assertSame('jane@example.com', $result->email);
        $this->assertSame('customer', $result->object);
        $this->assertSame('Jane Doe', $result->name);
    }

    public function testUpdateWithOptions(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new CustomersResource($client);

        $customerId = 'cus_test_123';
        $params = ['name' => 'Updated Name'];
        $options = ['expand' => ['sources']];

        // Expected payload after transformation (name moved to metadata)
        $expectedPayload = [
            'metadata' => [
                'name' => 'Updated Name',
            ],
        ];

        // API response with complete data for hybrid API
        $apiResponse = self::createCompleteCustomerData([
            'id' => $customerId,
            'name' => 'Updated Name',
            'metadata' => [
                'name' => 'Updated Name',
            ],
            'sources' => [
                [
                    'id' => 'src_123',
                    'type' => 'gcash',
                ],
            ],
        ]);

        $client->shouldReceive('put')
            ->once()
            ->with("customers/{$customerId}", $expectedPayload, $options)
            ->andReturn($apiResponse);

        $result = $resource->update($customerId, $params, $options);

        // Test array access (backward compatibility)
        $this->assertSame('Updated Name', $result['name']);
        $this->assertArrayHasKey('sources', $result);
        $this->assertSame('customer', $result['object']);
        
        // Test object access (new hybrid API)
        $this->assertSame('Updated Name', $result->name);
        $this->assertSame('customer', $result->object);
        $this->assertSame($customerId, $result->id);
    }

    public function testHandlesApiErrors(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new CustomersResource($client);

        $params = [
            'email' => 'invalid-email',  // Invalid email format
        ];

        $client->shouldReceive('post')
            ->once()
            ->with('customers', $params, [])
            ->andThrow(new MagpieException('Invalid email format', 'invalid_request_error'));

        $this->expectException(MagpieException::class);
        $this->expectExceptionMessage('Invalid email format');

        $resource->create($params);
    }

    public function testRetrieveByEmailWithOptions(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new CustomersResource($client);

        $email = 'test@example.com';
        $options = ['expand' => ['sources']];

        // API response with complete data for hybrid API
        $apiResponse = self::createCompleteCustomerData([
            'id' => 'cus_test_123',
            'email' => $email,
            'name' => 'Test User',
            'sources' => [
                ['id' => 'src_123'],
            ],
            'metadata' => [
                'name' => 'Test User',
            ],
        ]);

        $client->shouldReceive('request')
            ->once()
            ->with('GET', 'customers/by_email/' . $email, null, $options)
            ->andReturn($apiResponse);

        $result = $resource->retrieveByEmail($email, $options);

        // Test array access (backward compatibility)
        $this->assertSame($email, $result['email']);
        $this->assertArrayHasKey('sources', $result);
        $this->assertSame('customer', $result['object']);
        
        // Test object access (new hybrid API)
        $this->assertSame($email, $result->email);
        $this->assertSame('customer', $result->object);
        $this->assertSame('Test User', $result->name);
        $this->assertSame('cus_test_123', $result->id);
    }

    public function testAttachSourceWithOptions(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new CustomersResource($client);

        $customerId = 'cus_test_123';
        $sourceId = 'src_test_456';
        $options = ['expand' => ['sources.payments']];

        $expectedResponse = self::createCompleteCustomerData([
            'id' => $customerId,
            'sources' => [
                [
                    'id' => $sourceId,
                    'payments' => [],
                ],
            ],
        ]);

        $client->shouldReceive('request')
            ->once()
            ->with('POST', "customers/{$customerId}/sources", ['source' => $sourceId], $options)
            ->andReturn($expectedResponse);

        $result = $resource->attachSource($customerId, $sourceId, $options);

        // Test array access (backward compatibility)
        $this->assertSame($sourceId, $result['sources'][0]['id']);
        $this->assertArrayHasKey('payments', $result['sources'][0]);
        $this->assertSame('customer', $result['object']);
        
        // Test object access (new hybrid API)
        $this->assertSame($customerId, $result->id);
        $this->assertSame('customer', $result->object);
        $this->assertSame($sourceId, $result['sources'][0]['id']);
    }
}
