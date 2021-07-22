<?php

namespace Rector\Tests\Php81\Rector\Property\ReadOnlyPropertyRector\Fixture;

final class SkipWriteAlsoNormalProperty
{
    private string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function changeName(string $name): void
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }
}
