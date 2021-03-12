<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector\Fixture;

use Iterator;

final class SkipExistingType
{
    public function getValues(): Iterator
    {
        yield [100];
    }
}
