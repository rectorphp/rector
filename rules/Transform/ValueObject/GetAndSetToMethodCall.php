<?php

declare(strict_types=1);

namespace Rector\Transform\ValueObject;

use PHPStan\Type\ObjectType;

final class GetAndSetToMethodCall
{
    /**
     * @param class-string $classType
     */
    public function __construct(
        private string $classType,
        private string $getMethod,
        private string $setMethod
    ) {
    }

    public function getGetMethod(): string
    {
        return $this->getMethod;
    }

    public function getSetMethod(): string
    {
        return $this->setMethod;
    }

    public function getObjectType(): ObjectType
    {
        return new ObjectType($this->classType);
    }
}
