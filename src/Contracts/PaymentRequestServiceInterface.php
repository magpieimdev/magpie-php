<?php

declare(strict_types=1);

namespace Magpie\Contracts;

use Magpie\DTOs\Requests\PaymentRequests\CreatePaymentRequestRequest;
use Magpie\DTOs\Requests\PaymentRequests\VoidPaymentRequestRequest;
use Magpie\DTOs\Responses\PaymentRequest;

interface PaymentRequestServiceInterface
{
    public function create(CreatePaymentRequestRequest|array $request): PaymentRequest;
    
    public function retrieve(string $id): PaymentRequest;
    
    public function resend(string $id): PaymentRequest;
    
    public function void(string $id, VoidPaymentRequestRequest|array $request): PaymentRequest;
}