<?php

namespace Rector\Tests\Php81\Rector\Property\ReadOnlyPropertyRector\Fixture;

final class SkipWriteAlsoClonedProperty
{
    private string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function withName(string $name): self
    {
        $clone = clone $this;
        $clone->name = $name;

        return $clone;
    }

    public function getName()
    {
        return $this->name;
    }
}
