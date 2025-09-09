<?php

declare(strict_types=1);

namespace Magpie\DTOs\Requests\Charges;

use Magpie\DTOs\Requests\BaseRequest;

class CreateChargeRequest extends BaseRequest
{
    public function __construct(
        public readonly int $amount,
        public readonly string $currency,
        public readonly string $source,
        public readonly string $description,
        public readonly string $statement_descriptor,
        public readonly bool $capture = true,
        public readonly ?string $cvc = null,
        public readonly ?bool $require_auth = null,
        public readonly ?string $redirect_url = null,
        public readonly array $metadata = []
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if ($this->amount <= 0) {
            throw new \InvalidArgumentException('Amount must be greater than 0');
        }

        if (3 !== strlen($this->currency)) {
            throw new \InvalidArgumentException('Currency must be a 3-letter ISO code');
        }

        if (empty($this->source)) {
            throw new \InvalidArgumentException('Source cannot be empty');
        }

        if (empty($this->description)) {
            throw new \InvalidArgumentException('Description cannot be empty');
        }

        if (empty($this->statement_descriptor)) {
            throw new \InvalidArgumentException('Statement descriptor cannot be empty');
        }

        if (strlen($this->statement_descriptor) > 15) {
            throw new \InvalidArgumentException('Statement descriptor must be 15 characters or less');
        }
    }
}
