<?php

declare(strict_types=1);

namespace Rector\Arguments\ValueObject;

use PHPStan\Type\ObjectType;

final class ValueObjectWrapArg
{
    public function __construct(
        private string $objectType,
        private string $methodName,
        private int $argPosition,
        private string $newType
    ) {
    }

    public function getObjectType(): ObjectType
    {
        return new ObjectType($this->objectType);
    }

    public function getMethodName(): string
    {
        return $this->methodName;
    }

    public function getArgPosition(): int
    {
        return $this->argPosition;
    }

    public function getNewType(): ObjectType
    {
        return new ObjectType($this->newType);
    }
}
