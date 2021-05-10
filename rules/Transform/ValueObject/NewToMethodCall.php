<?php

declare(strict_types=1);

namespace Rector\Transform\ValueObject;

use PHPStan\Type\ObjectType;

final class NewToMethodCall
{
    public function __construct(
        private string $newType,
        private string $serviceType,
        private string $serviceMethod
    ) {
    }

    public function getNewObjectType(): ObjectType
    {
        return new ObjectType($this->newType);
    }

    public function getServiceObjectType(): ObjectType
    {
        return new ObjectType($this->serviceType);
    }

    public function getServiceMethod(): string
    {
        return $this->serviceMethod;
    }
}
