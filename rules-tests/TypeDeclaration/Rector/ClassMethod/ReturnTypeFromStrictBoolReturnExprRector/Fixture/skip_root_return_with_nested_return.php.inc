<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictBoolReturnExprRector\Fixture;

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
