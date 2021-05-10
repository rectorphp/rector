<?php

declare(strict_types=1);

namespace Rector\Transform\ValueObject;

use PHPStan\Type\ObjectType;

final class ServiceGetterToConstructorInjection
{
    public function __construct(
        private string $oldType,
        private string $oldMethod,
        private string $serviceType
    ) {
    }

    public function getOldObjectType(): ObjectType
    {
        return new ObjectType($this->oldType);
    }

    public function getOldMethod(): string
    {
        return $this->oldMethod;
    }

    public function getServiceType(): string
    {
        return $this->serviceType;
    }
}
