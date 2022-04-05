<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector\Fixture;

class SkipInnerClass2
{
    /**
     * @var string
     */
    private $name;

    public function __construct()
    {
        new class {
            public function __construct()
            {
                $this->name = 'value';
            }
        };
    }
}
