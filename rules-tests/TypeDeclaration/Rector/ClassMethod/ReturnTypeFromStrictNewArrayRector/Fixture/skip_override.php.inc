<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictNewArrayRector\Fixture;

final class SkipOverride
{
    public function run()
    {
        $values = [];

        $values = 100;

        return $values;
    }
}
