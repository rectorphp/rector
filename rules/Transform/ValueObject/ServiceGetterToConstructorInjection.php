<?php

declare(strict_types=1);

namespace Rector\Transform\ValueObject;

use PHPStan\Type\ObjectType;
use Rector\Core\Validation\RectorAssert;

final class ServiceGetterToConstructorInjection
{
    public function __construct(
        private readonly string $oldType,
        private readonly string $oldMethod,
        private readonly string $serviceType
    ) {
        RectorAssert::className($oldType);
        RectorAssert::className($serviceType);
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
