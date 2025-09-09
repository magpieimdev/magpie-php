<?php

declare(strict_types=1);

namespace Magpie\DTOs\ValueObjects;

/**
 * Action required to complete a charge (e.g., 3D Secure authentication).
 * 
 * Some charges require additional customer interaction to complete,
 * such as entering an OTP or completing 3D Secure authentication.
 */
class ChargeAction
{
    public function __construct(
        /** The action type */
        public readonly string $type,
        /** The action URL */
        public readonly string $url
    ) {}

    /**
     * Create a ChargeAction from an array.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            type: $data['type'],
            url: $data['url']
        );
    }

    /**
     * Convert the ChargeAction to an array.
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'url' => $this->url,
        ];
    }
}