<?php

namespace Rector\TypeDeclaration\Tests\Rector\Property\TypedPropertyFromStrictConstructorRector\Fixture;

class SkipAlreadyType
{
    private string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }
}
