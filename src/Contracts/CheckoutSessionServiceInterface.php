<?php

declare(strict_types=1);

namespace Magpie\Contracts;

use Magpie\DTOs\Requests\Checkout\CreateCheckoutSessionRequest;
use Magpie\DTOs\Requests\Checkout\CaptureSessionRequest;
use Magpie\DTOs\Responses\CheckoutSession;

interface CheckoutSessionServiceInterface
{
    public function create(CreateCheckoutSessionRequest|array $request): CheckoutSession;
    
    public function retrieve(string $id): CheckoutSession;
    
    public function capture(string $id, CaptureSessionRequest|array $request): CheckoutSession;
    
    public function expire(string $id): CheckoutSession;
}