<?php

declare(strict_types=1);

namespace Magpie\Contracts;

use Magpie\DTOs\Requests\PaymentLinks\CreatePaymentLinkRequest;
use Magpie\DTOs\Requests\PaymentLinks\UpdatePaymentLinkRequest;
use Magpie\DTOs\Responses\PaymentLink;

interface PaymentLinkServiceInterface
{
    public function create(CreatePaymentLinkRequest|array $request): PaymentLink;
    
    public function retrieve(string $id): PaymentLink;
    
    public function update(string $id, UpdatePaymentLinkRequest|array $request): PaymentLink;
    
    public function activate(string $id): PaymentLink;
    
    public function deactivate(string $id): PaymentLink;
}