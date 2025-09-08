<?php

declare(strict_types=1);

namespace Magpie\Contracts;

use Magpie\DTOs\Requests\Charges\CreateChargeRequest;
use Magpie\DTOs\Requests\Charges\CaptureChargeRequest;
use Magpie\DTOs\Requests\Charges\RefundChargeRequest;
use Magpie\DTOs\Responses\Charge;

interface ChargeServiceInterface
{
    public function create(CreateChargeRequest|array $request): Charge;
    
    public function retrieve(string $id): Charge;
    
    public function capture(string $id, CaptureChargeRequest|array $request): Charge;
    
    public function refund(string $id, RefundChargeRequest|array $request): Charge;
    
    public function void(string $id): Charge;
}