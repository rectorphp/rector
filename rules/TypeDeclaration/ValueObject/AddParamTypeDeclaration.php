<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\ValueObject;

use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;

final class AddParamTypeDeclaration
{
    public function __construct(
        private string $className,
        private string $methodName,
        private int $position,
        private Type $paramType
    ) {
    }

    public function getObjectType(): ObjectType
    {
        return new ObjectType($this->className);
    }

    public function getMethodName(): string
    {
        return $this->methodName;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function getParamType(): Type
    {
        return $this->paramType;
    }
}
