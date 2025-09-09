<?php

declare(strict_types=1);

namespace Magpie\Contracts;

use Magpie\DTOs\Requests\Customers\CreateCustomerRequest;
use Magpie\DTOs\Requests\Customers\UpdateCustomerRequest;

interface CustomerServiceInterface
{
    public function create(CreateCustomerRequest|array $request, array $options = []): mixed;

    public function retrieve(string $id, array $options = []): mixed;

    public function update(string $id, UpdateCustomerRequest|array $request, array $options = []): mixed;

    public function retrieveByEmail(string $email, array $options = []): mixed;

    public function attachSource(string $customerId, string $sourceId, array $options = []): mixed;

    public function detachSource(string $customerId, string $sourceId, array $options = []): mixed;
}
