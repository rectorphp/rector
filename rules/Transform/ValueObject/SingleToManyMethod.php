<?php

declare(strict_types=1);

namespace Rector\Transform\ValueObject;

use PHPStan\Type\ObjectType;

final class SingleToManyMethod
{
    public function __construct(
        private string $class,
        private string $singleMethodName,
        private string $manyMethodName
    ) {
    }

    public function getObjectType(): ObjectType
    {
        return new ObjectType($this->class);
    }

    public function getSingleMethodName(): string
    {
        return $this->singleMethodName;
    }

    public function getManyMethodName(): string
    {
        return $this->manyMethodName;
    }
}
