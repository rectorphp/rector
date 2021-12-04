<?php

declare(strict_types=1);

namespace Rector\Transform\ValueObject;

final class PropertyAndClassMethodName
{
    public function __construct(
        private readonly string $propertyName,
        private readonly string $classMethodName
    ) {
    }

    public function getPropertyName(): string
    {
        return $this->propertyName;
    }

    public function getClassMethodName(): string
    {
        return $this->classMethodName;
    }
}
