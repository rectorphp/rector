<?php

declare(strict_types=1);

namespace Rector\Renaming\ValueObject;

use PHPStan\Type\ObjectType;

final class RenameProperty
{
    public function __construct(
        private string $type,
        private string $oldProperty,
        private string $newProperty
    ) {
    }

    public function getObjectType(): ObjectType
    {
        return new ObjectType($this->type);
    }

    public function getOldProperty(): string
    {
        return $this->oldProperty;
    }

    public function getNewProperty(): string
    {
        return $this->newProperty;
    }
}
