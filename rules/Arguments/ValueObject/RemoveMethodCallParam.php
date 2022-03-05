<?php

declare(strict_types=1);

namespace Rector\Arguments\ValueObject;

use PHPStan\Type\ObjectType;

final class RemoveMethodCallParam
{
    public function __construct(
        private readonly string $class,
        private readonly string $methodName,
        private readonly int $paramPosition
    ) {
    }

    public function getObjectType(): ObjectType
    {
        return new ObjectType($this->class);
    }

    public function getMethodName(): string
    {
        return $this->methodName;
    }

    public function getParamPosition(): int
    {
        return $this->paramPosition;
    }
}
