<?php

declare(strict_types=1);

namespace Magpie\Contracts;

use Magpie\DTOs\Requests\PaymentRequests\CreatePaymentRequestRequest;
use Magpie\DTOs\Requests\PaymentRequests\VoidPaymentRequestRequest;

interface PaymentRequestServiceInterface
{
    public function create(CreatePaymentRequestRequest|array $request, array $options = []): mixed;

    public function retrieve(string $id, array $options = []): mixed;

    public function resend(string $id, array $options = []): mixed;

    public function void(string $id, VoidPaymentRequestRequest|array $request, array $options = []): mixed;
}
