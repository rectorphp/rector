<?php

declare(strict_types=1);

namespace Rector\Generics\ValueObject;

final class GenericClassMethodParam
{
    public function __construct(
        private string $classType,
        private string $methodName,
        private int $paramPosition,
        private string $paramGenericType
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
