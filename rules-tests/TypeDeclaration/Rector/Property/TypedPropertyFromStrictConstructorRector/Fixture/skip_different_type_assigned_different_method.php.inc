<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector\Fixture;

class SkipDifferentTypeAssignedDifferentMethod
{
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function reset(int $name)
    {
        $this->name = $name;
    }
}
