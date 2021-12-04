<?php

declare(strict_types=1);

namespace Rector\Transform\ValueObject;

use PHPStan\Type\ObjectType;

final class CallableInMethodCallToVariable
{
    public function __construct(
        private readonly string $classType,
        private readonly string $methodName,
        private readonly int $argumentPosition
    ) {
    }

    public function getObjectType(): ObjectType
    {
        return new ObjectType($this->classType);
    }

    public function getMethodName(): string
    {
        return $this->methodName;
    }

    public function getArgumentPosition(): int
    {
        return $this->argumentPosition;
    }
}
