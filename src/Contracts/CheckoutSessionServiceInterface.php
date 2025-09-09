<?php

declare(strict_types=1);

namespace Magpie\Contracts;

use Magpie\DTOs\Requests\Checkout\CaptureSessionRequest;
use Magpie\DTOs\Requests\Checkout\CreateCheckoutSessionRequest;

interface CheckoutSessionServiceInterface
{
    public function create(CreateCheckoutSessionRequest|array $request, array $options = []): mixed;

    public function retrieve(string $id, array $options = []): mixed;

    public function capture(string $id, CaptureSessionRequest|array $request, array $options = []): mixed;

    public function expire(string $id, array $options = []): mixed;
}
