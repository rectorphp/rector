<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture;

use Iterator;
use Traversable;

class SkipIterableArrayIteratorCoType
{
    public function run(): Iterator
    {
        yield 5;
    }

    public function run2(): Traversable
    {
        yield 5;
    }
}
