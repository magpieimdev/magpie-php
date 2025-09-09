<?php

declare(strict_types=1);

namespace Magpie\DTOs\Requests;

abstract class BaseRequest
{
    public static function fromArray(array $data): static
    {
        $reflection = new \ReflectionClass(static::class);
        $constructor = $reflection->getConstructor();

        if (! $constructor) {
            /* @var static */
            return new static();
        }

        $parameters = $constructor->getParameters();
        $args = [];

        foreach ($parameters as $parameter) {
            $key = $parameter->getName();
            $value = $data[$key] ?? null;

            if (null !== $value) {
                $args[] = $value;
            } elseif ($parameter->isDefaultValueAvailable()) {
                $args[] = $parameter->getDefaultValue();
            } else {
                $args[] = null;
            }
        }

        /* @var static */
        return new static(...$args);
    }

    public function toArray(): array
    {
        $reflection = new \ReflectionClass($this);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);

        $data = [];
        foreach ($properties as $property) {
            $value = $property->getValue($this);

            if (null !== $value) {
                $key = $this->convertPropertyName($property->getName());
                $data[$key] = $this->convertValue($value);
            }
        }

        return $data;
    }

    protected function convertPropertyName(string $propertyName): string
    {
        return strtolower(preg_replace('/([A-Z])/', '_$1', lcfirst($propertyName)));
    }

    protected function convertValue(mixed $value): mixed
    {
        if ($value instanceof \BackedEnum) {
            return $value->value;
        }

        if ($value instanceof \DateTime) {
            return $value->getTimestamp();
        }

        // Handle value objects with toArray() method
        if (is_object($value) && method_exists($value, 'toArray')) {
            return $value->toArray();
        }

        if (is_array($value)) {
            return array_map(fn ($item) => $this->convertValue($item), $value);
        }

        return $value;
    }
}
