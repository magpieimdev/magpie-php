<?php

declare(strict_types=1);

namespace Magpie\DTOs\ValueObjects;

/**
 * Branding configuration for payment pages and receipts.
 *
 * Controls the visual appearance of checkout pages, payment links,
 * and other customer-facing payment interfaces.
 */
class BrandingOptions
{
    public function __construct(
        /** URL to an icon image. */
        public readonly ?string $icon = null,
        /** URL to a logo image. */
        public readonly ?string $logo = null,
        /** Whether to use the logo. */
        public readonly bool $use_logo = false,
        /** A CSS color value representing the primary branding color. */
        public readonly string $primary_color = '',
        /** A CSS color value representing the secondary branding color. */
        public readonly string $secondary_color = ''
    ) {
    }

    /**
     * Create a BrandingOptions from an array.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            icon: $data['icon'] ?? null,
            logo: $data['logo'] ?? null,
            use_logo: $data['use_logo'] ?? false,
            primary_color: $data['primary_color'] ?? '',
            secondary_color: $data['secondary_color'] ?? ''
        );
    }

    /**
     * Convert the BrandingOptions to an array.
     */
    public function toArray(): array
    {
        return array_filter([
            'icon' => $this->icon,
            'logo' => $this->logo,
            'use_logo' => $this->use_logo,
            'primary_color' => $this->primary_color,
            'secondary_color' => $this->secondary_color,
        ], fn ($value) => null !== $value && '' !== $value);
    }
}
