<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector\Fixture;

class SkipDifferentTypeAssignedDifferentMethod2
{
    private $name;

    public function __construct()
    {
        $this->name = 'test';
    }

    public function reset(int $name)
    {
        $this->name = $name;
    }
}
