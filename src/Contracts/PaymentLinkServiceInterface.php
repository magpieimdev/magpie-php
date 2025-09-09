<?php

declare(strict_types=1);

namespace Magpie\Contracts;

use Magpie\DTOs\Requests\PaymentLinks\CreatePaymentLinkRequest;
use Magpie\DTOs\Requests\PaymentLinks\UpdatePaymentLinkRequest;

interface PaymentLinkServiceInterface
{
    public function create(CreatePaymentLinkRequest|array $request, array $options = []): mixed;

    public function retrieve(string $id, array $options = []): mixed;

    public function update(string $id, UpdatePaymentLinkRequest|array $request, array $options = []): mixed;

    public function activate(string $id, array $options = []): mixed;

    public function deactivate(string $id, array $options = []): mixed;
}
