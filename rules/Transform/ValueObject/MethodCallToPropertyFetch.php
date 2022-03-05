<?php

declare(strict_types=1);

namespace Rector\Transform\ValueObject;

use PHPStan\Type\ObjectType;
use Rector\Core\Validation\RectorAssert;

final class MethodCallToPropertyFetch
{
    public function __construct(
        private readonly string $oldType,
        private readonly string $oldMethod,
        private readonly string $newProperty,
    ) {
        RectorAssert::className($oldType);
    }

    public function getOldObjectType(): ObjectType
    {
        return new ObjectType($this->oldType);
    }

    public function getNewProperty(): string
    {
        return $this->newProperty;
    }

    public function getOldMethod(): string
    {
        return $this->oldMethod;
    }
}
