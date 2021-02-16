<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector\Fixture;

final class SkipYield
{
    public function getValues()
    {
        yield [1000];
    }
}
