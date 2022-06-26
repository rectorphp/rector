<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictBoolReturnExprRector\Fixture;

final class SkipNestedReturn
{
    public function run(array $values)
    {
        foreach ($values as $value) {
            return true;
        }
    }
}
