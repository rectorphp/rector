<?php

declare(strict_types=1);

namespace Rector\Transform\ValueObject;

use PHPStan\Type\ObjectType;

final class ParentClassToTraits
{
    /**
     * @var string
     */
    private $parentType;

    /**
     * @var string[]
     */
    private $traitNames = [];

    /**
     * @param string[] $traitNames
     */
    public function __construct(string $parentType, array $traitNames)
    {
        $this->parentType = $parentType;
        $this->traitNames = $traitNames;
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
