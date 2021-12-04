<?php

declare(strict_types=1);

namespace Rector\Generics\ValueObject;

final class GenericClassMethodParam
{
    public function __construct(
        private readonly string $classType,
        private readonly string $methodName,
        private readonly int $paramPosition,
        private readonly string $paramGenericType
    ) {
    }

    public function getClassType(): string
    {
        return $this->classType;
    }

    public function getMethodName(): string
    {
        return $this->methodName;
    }

    public function getParamPosition(): int
    {
        return $this->paramPosition;
    }

    public function getParamGenericType(): string
    {
        return $this->paramGenericType;
    }
}
