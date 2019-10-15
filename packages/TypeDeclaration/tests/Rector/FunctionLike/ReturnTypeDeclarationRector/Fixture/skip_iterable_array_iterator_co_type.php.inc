<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture;

use Iterator;

class SkipIterableArrayIteratorCoType
{
    public function run(): Iterator
    {
        yield 5;
    }
}
