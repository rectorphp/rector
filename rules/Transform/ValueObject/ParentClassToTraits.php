<?php

declare(strict_types=1);

namespace Rector\Transform\ValueObject;

final class ParentClassToTraits
{
    /**
     * @param string[] $traitNames
     */
    public function __construct(
        private readonly string $parentType,
        private readonly array $traitNames
    ) {
    }

    public function getParentType(): string
    {
        return $this->parentType;
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
