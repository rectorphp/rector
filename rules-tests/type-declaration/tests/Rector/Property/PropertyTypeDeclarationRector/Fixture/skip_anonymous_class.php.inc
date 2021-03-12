<?php

namespace Rector\TypeDeclaration\Tests\Rector\Property\PropertyTypeDeclarationRector\Fixture;

class SkipAnonymousClass
{
    public function makeClass(int $bar = 1)
    {
        $class = new class() {
            public $bar;
        };

        $class->bar = $bar;
    }
}
