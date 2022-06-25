<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictReturnExprRector\Fixture;

final class SkipRootReturnWithNestedReturn
{
    public function run(array $values)
    {
        foreach ($values as $value) {
            return $value;
        }

        return true;
    }
}
