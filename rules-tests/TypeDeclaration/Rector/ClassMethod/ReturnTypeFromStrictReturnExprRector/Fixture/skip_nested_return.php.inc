<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictReturnExprRector\Fixture;

final class SkipNestedReturn
{
    public function run(array $values)
    {
        foreach ($values as $value) {
            return true;
        }
    }
}
