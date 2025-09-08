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

        // API response has name in metadata
        $apiResponse = [
            'id' => 'cus_test_123',
            'object' => 'customer',
            'email' => 'john@example.com',
            'phone' => '+639151234567',
            'description' => 'Premium customer',
            'created' => 1640995200,
            'metadata' => [
                'user_id' => '12345',
                'plan' => 'premium',
                'name' => 'John Doe',
            ],
        ];

        $client->shouldReceive('post')
            ->once()
            ->with('customers', $expectedPayload, [])
            ->andReturn($apiResponse);

        $result = $resource->create($params);

        $this->assertSame('cus_test_123', $result['id']);
        $this->assertSame('customer', $result['object']);
        $this->assertSame('John Doe', $result['name']); // Should be extracted from metadata
        $this->assertSame('john@example.com', $result['email']);
        $this->assertSame('+639151234567', $result['phone']);
        $this->assertSame('Premium customer', $result['description']);
        $this->assertSame('12345', $result['metadata']['user_id']);
        $this->assertSame('premium', $result['metadata']['plan']);
        $this->assertSame('John Doe', $result['metadata']['name']); // Should still be in metadata
    }

    public function testRetrieve(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new CustomersResource($client);

        $customerId = 'cus_test_123';
        $expectedResponse = [
            'id' => $customerId,
            'object' => 'customer',
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'default_source' => 'src_456',
            'sources' => [
                [
                    'id' => 'src_456',
                    'type' => 'gcash',
                ],
            ],
        ];

        $client->shouldReceive('get')
            ->once()
            ->with("customers/{$customerId}", null, [])
            ->andReturn($expectedResponse);

        $result = $resource->retrieve($customerId);

        $this->assertSame($customerId, $result['id']);
        $this->assertSame('John Doe', $result['name']);
        $this->assertSame('john@example.com', $result['email']);
        $this->assertSame('src_456', $result['default_source']);
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

        // API response has name in metadata
        $apiResponse = [
            'id' => $customerId,
            'object' => 'customer',
            'email' => 'john@example.com',
            'phone' => '+639157654321',
            'metadata' => [
                'user_id' => '12345',
                'plan' => 'enterprise',
                'name' => 'John Smith',
            ],
        ];

        $client->shouldReceive('put')
            ->once()
            ->with("customers/{$customerId}", $expectedPayload, [])
            ->andReturn($apiResponse);

        $result = $resource->update($customerId, $params);

        $this->assertSame($customerId, $result['id']);
        $this->assertSame('John Smith', $result['name']);
        $this->assertSame('+639157654321', $result['phone']);
        $this->assertSame('enterprise', $result['metadata']['plan']);
    }

    public function testRetrieveByEmail(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new CustomersResource($client);

        $email = 'john@example.com';
        
        // API response has name in metadata
        $apiResponse = [
            'id' => 'cus_test_123',
            'object' => 'customer',
            'email' => $email,
            'phone' => '+639151234567',
            'metadata' => [
                'name' => 'John Doe',
            ],
        ];

        $client->shouldReceive('request')
            ->once()
            ->with('GET', 'customers/by_email/' . $email, null, [])
            ->andReturn($apiResponse);

        $result = $resource->retrieveByEmail($email);

        $this->assertSame('cus_test_123', $result['id']);
        $this->assertSame($email, $result['email']);
        $this->assertSame('John Doe', $result['name']);
    }

    public function testAttachSource(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new CustomersResource($client);

        $customerId = 'cus_test_123';
        $sourceId = 'src_test_456';

        $expectedResponse = [
            'id' => $customerId,
            'object' => 'customer',
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'default_source' => $sourceId,
            'sources' => [
                [
                    'id' => $sourceId,
                    'type' => 'gcash',
                    'status' => 'verified',
                ],
            ],
        ];

        $client->shouldReceive('request')
            ->once()
            ->with('POST', "customers/{$customerId}/sources", ['source' => $sourceId], [])
            ->andReturn($expectedResponse);

        $result = $resource->attachSource($customerId, $sourceId);

        $this->assertSame($customerId, $result['id']);
        $this->assertSame($sourceId, $result['default_source']);
        $this->assertSame($sourceId, $result['sources'][0]['id']);
    }

    public function testDetachSource(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new CustomersResource($client);

        $customerId = 'cus_test_123';
        $sourceId = 'src_test_456';

        $expectedResponse = [
            'id' => $customerId,
            'object' => 'customer',
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'default_source' => null,
            'sources' => [],
        ];

        $client->shouldReceive('request')
            ->once()
            ->with('DELETE', "customers/{$customerId}/sources/{$sourceId}", null, [])
            ->andReturn($expectedResponse);

        $result = $resource->detachSource($customerId, $sourceId);

        $this->assertSame($customerId, $result['id']);
        $this->assertNull($result['default_source']);
        $this->assertEmpty($result['sources']);
    }

    public function testCreateWithMinimalParams(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new CustomersResource($client);

        $params = [
            'email' => 'minimal@example.com',
        ];

        $expectedResponse = [
            'id' => 'cus_test_456',
            'object' => 'customer',
            'email' => 'minimal@example.com',
            'name' => null,
            'phone' => null,
        ];

        $client->shouldReceive('post')
            ->once()
            ->with('customers', $params, [])
            ->andReturn($expectedResponse);

        $result = $resource->create($params);

        $this->assertSame('cus_test_456', $result['id']);
        $this->assertSame('minimal@example.com', $result['email']);
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

        // API response has name in metadata
        $apiResponse = [
            'id' => 'cus_test_789',
            'object' => 'customer',
            'metadata' => [
                'name' => 'Jane Doe',
            ],
        ];

        $client->shouldReceive('post')
            ->once()
            ->with('customers', $expectedPayload, $options)
            ->andReturn($apiResponse);

        $result = $resource->create($params, $options);

        $this->assertSame('cus_test_789', $result['id']);
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

        // API response has name in metadata
        $apiResponse = [
            'id' => $customerId,
            'object' => 'customer',
            'metadata' => [
                'name' => 'Updated Name',
            ],
            'sources' => [
                [
                    'id' => 'src_123',
                    'type' => 'gcash',
                ],
            ],
        ];

        $client->shouldReceive('put')
            ->once()
            ->with("customers/{$customerId}", $expectedPayload, $options)
            ->andReturn($apiResponse);

        $result = $resource->update($customerId, $params, $options);

        $this->assertSame('Updated Name', $result['name']);
        $this->assertArrayHasKey('sources', $result);
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

        // API response
        $apiResponse = [
            'id' => 'cus_test_123',
            'email' => $email,
            'sources' => [
                ['id' => 'src_123'],
            ],
            'metadata' => [
                'name' => 'Test User',
            ],
        ];

        $client->shouldReceive('request')
            ->once()
            ->with('GET', 'customers/by_email/' . $email, null, $options)
            ->andReturn($apiResponse);

        $result = $resource->retrieveByEmail($email, $options);

        $this->assertSame($email, $result['email']);
        $this->assertArrayHasKey('sources', $result);
    }

    public function testAttachSourceWithOptions(): void
    {
        $client = \Mockery::mock(Client::class);
        $resource = new CustomersResource($client);

        $customerId = 'cus_test_123';
        $sourceId = 'src_test_456';
        $options = ['expand' => ['sources.payments']];

        $expectedResponse = [
            'id' => $customerId,
            'default_source' => $sourceId,
            'sources' => [
                [
                    'id' => $sourceId,
                    'payments' => [],
                ],
            ],
        ];

        $client->shouldReceive('request')
            ->once()
            ->with('POST', "customers/{$customerId}/sources", ['source' => $sourceId], $options)
            ->andReturn($expectedResponse);

        $result = $resource->attachSource($customerId, $sourceId, $options);

        $this->assertSame($sourceId, $result['default_source']);
        $this->assertArrayHasKey('payments', $result['sources'][0]);
    }
}
