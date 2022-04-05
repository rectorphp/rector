<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector\Fixture;

class SkipInnerClass
{
    /**
     * @var string
     */
    private $name;

    public function __construct(string $name)
    {
        new class {
            public function __construct(string $name)
            {
                $this->name = $name;
            }
        };
    }
}
