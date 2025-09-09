<?php

declare(strict_types=1);

namespace Magpie\DTOs\Responses;

abstract class BaseResponse implements \ArrayAccess
{
    private array $_data = [];

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
                // Handle type conversions
                $type = $parameter->getType();
                if ($type instanceof \ReflectionNamedType) {
                    $typeName = $type->getName();

                    // Handle enum conversion
                    if (enum_exists($typeName) && is_string($value)) {
                        /** @var \UnitEnum $enumClass */
                        $enumClass = $typeName;
                        if (method_exists($enumClass, 'from')) {
                            $value = $enumClass::from($value);
                        }
                    }
                    // Handle complex object construction
                    elseif (class_exists($typeName) && is_array($value) && method_exists($typeName, 'fromArray')) {
                        $value = $typeName::fromArray($value);
                    }
                }
                $args[] = $value;
            } elseif ($parameter->isDefaultValueAvailable()) {
                $args[] = $parameter->getDefaultValue();
            } else {
                $args[] = null;
            }
        }

        /** @var static */
        $instance = new static(...$args);
        $instance->_data = $data;

        return $instance;
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

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->_data[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->_data[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (null === $offset) {
            $this->_data[] = $value;
        } else {
            $this->_data[$offset] = $value;
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->_data[$offset]);
    }
}
