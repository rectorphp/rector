<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector\Fixture;

final class SkipYield
{
    public function getValues()
    {
        yield [1000];
    }
}
