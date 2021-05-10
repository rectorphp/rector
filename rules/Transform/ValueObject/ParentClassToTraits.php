<?php

declare(strict_types=1);

namespace Rector\Transform\ValueObject;

use PHPStan\Type\ObjectType;

final class ParentClassToTraits
{
    /**
     * @param string[] $traitNames
     */
    public function __construct(
        private string $parentType,
        private array $traitNames
    ) {
    }

    public function getParentObjectType(): ObjectType
    {
        return new ObjectType($this->parentType);
    }

    /**
     * @return string[]
     */
    public function getTraitNames(): array
    {
        // keep the Trait order the way it is in config
        return array_reverse($this->traitNames);
    }
}
