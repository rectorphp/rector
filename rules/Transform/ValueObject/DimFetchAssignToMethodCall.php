<?php

declare(strict_types=1);

namespace Rector\Transform\ValueObject;

use PHPStan\Type\ObjectType;

final class DimFetchAssignToMethodCall
{
    public function __construct(
        private string $listClass,
        private string $itemClass,
        private string $addMethod
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
