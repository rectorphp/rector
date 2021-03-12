<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector\Fixture;

class SkipAlreadyType
{
    private string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }
}
