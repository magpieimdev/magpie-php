<?php

declare(strict_types=1);

namespace Magpie\DTOs\ValueObjects;

/**
 * Information about a failed charge.
 * 
 * When a charge fails, this interface provides detailed information
 * about why it failed and suggested next steps for resolution.
 */
class ChargeFailure
{
    public function __construct(
        /** The failure reason */
        public readonly string $reason,
        /** The failure code */
        public readonly string $code,
        /** The failure next steps */
        public readonly string $next_steps,
        /** The failure provider response */
        public readonly ChargeProviderResponse $provider_response
    ) {}

    /**
     * Create a ChargeFailure from an array.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            reason: $data['reason'],
            code: $data['code'],
            next_steps: $data['next_steps'],
            provider_response: ChargeProviderResponse::fromArray($data['provider_response'])
        );
    }

    /**
     * Convert the ChargeFailure to an array.
     */
    public function toArray(): array
    {
        return [
            'reason' => $this->reason,
            'code' => $this->code,
            'next_steps' => $this->next_steps,
            'provider_response' => $this->provider_response->toArray(),
        ];
    }
}