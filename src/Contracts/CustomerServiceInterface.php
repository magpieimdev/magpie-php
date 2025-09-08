<?php

declare(strict_types=1);

namespace Magpie\Contracts;

use Magpie\DTOs\Requests\Customers\CreateCustomerRequest;
use Magpie\DTOs\Requests\Customers\UpdateCustomerRequest;
use Magpie\DTOs\Responses\Customer;

interface CustomerServiceInterface
{
    public function create(CreateCustomerRequest|array $request): Customer;
    
    public function retrieve(string $id): Customer;
    
    public function update(string $id, UpdateCustomerRequest|array $request): Customer;
    
    public function retrieveByEmail(string $email): Customer;
    
    public function attachSource(string $customerId, string $sourceId): Customer;
    
    public function detachSource(string $customerId, string $sourceId): Customer;
}