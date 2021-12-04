<?php

declare(strict_types=1);

namespace Rector\Transform\ValueObject;

use PHPStan\Type\ObjectType;

final class DimFetchAssignToMethodCall
{
    public function __construct(
        private readonly string $listClass,
        private readonly string $itemClass,
        private readonly string $addMethod
    ) {
    }

    public function getListObjectType(): ObjectType
    {
        return new ObjectType($this->listClass);
    }

    public function getItemObjectType(): ObjectType
    {
        return new ObjectType($this->itemClass);
    }

    public function getAddMethod(): string
    {
        return $this->addMethod;
    }
}
