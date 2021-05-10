<?php

declare(strict_types=1);

namespace Rector\Transform\ValueObject;

use PHPStan\Type\ObjectType;

final class FuncCallToMethodCall
{
    public function __construct(
        private string $oldFuncName,
        private string $newClassName,
        private string $newMethodName
    ) {
    }

    public function getOldFuncName(): string
    {
        return $this->oldFuncName;
    }

    public function getNewObjectType(): ObjectType
    {
        return new ObjectType($this->newClassName);
    }

    public function getNewMethodName(): string
    {
        return $this->newMethodName;
    }
}
