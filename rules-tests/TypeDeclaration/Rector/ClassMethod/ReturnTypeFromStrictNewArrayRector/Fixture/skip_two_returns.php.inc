<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictNewArrayRector\Fixture;

final class SkipTwoReturns
{
    public function run()
    {
        $values = [];

        if (mt_rand(1, 2)) {
            return $values;
        }

        return null;
    }
}
