<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictBoolReturnExprRector\Fixture;

final class SkipMultiReturnsNoRoot
{
    public function run(array $values)
    {
        foreach ($values as $value) {
            return true;
        }

        if ($values === []) {
            return false;
        }
    }
}
