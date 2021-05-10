<?php

declare(strict_types=1);

namespace Rector\Transform\ValueObject;

use PHPStan\Type\ObjectType;

final class PropertyAssignToMethodCall
{
    public function __construct(
        private string $class,
        private string $oldPropertyName,
        private string $newMethodName
    ) {
    }

    public function getObjectType(): ObjectType
    {
        return new ObjectType($this->class);
    }

    public function getOldPropertyName(): string
    {
        return $this->oldPropertyName;
    }

    public function getNewMethodName(): string
    {
        return $this->newMethodName;
    }
}
