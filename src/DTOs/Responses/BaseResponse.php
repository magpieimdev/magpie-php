<?php

declare(strict_types=1);

namespace Magpie\DTOs\Responses;

abstract class BaseResponse
{
    public static function fromArray(array $data): static
    {
        $reflection = new \ReflectionClass(static::class);
        $constructor = $reflection->getConstructor();
        
        if (!$constructor) {
            return new static();
        }
        
        $parameters = $constructor->getParameters();
        $args = [];
        
        foreach ($parameters as $parameter) {
            $key = $parameter->getName();
            $value = $data[$key] ?? null;
            
            if ($value !== null) {
                $args[] = $value;
            } else if ($parameter->isDefaultValueAvailable()) {
                $args[] = $parameter->getDefaultValue();
            } else {
                $args[] = null;
            }
        }
        
        return new static(...$args);
    }
}