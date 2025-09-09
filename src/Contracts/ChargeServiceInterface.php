<?php

declare(strict_types=1);

namespace Magpie\Contracts;

use Magpie\DTOs\Requests\Charges\CaptureChargeRequest;
use Magpie\DTOs\Requests\Charges\CreateChargeRequest;
use Magpie\DTOs\Requests\Charges\RefundChargeRequest;

interface ChargeServiceInterface
{
    public function create(CreateChargeRequest|array $request, array $options = []): mixed;

    public function retrieve(string $id, array $options = []): mixed;

    public function capture(string $id, CaptureChargeRequest|array $request, array $options = []): mixed;

    public function refund(string $id, RefundChargeRequest|array $request, array $options = []): mixed;

    public function void(string $id, array $options = []): mixed;
}
