<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector\Fixture;

final class SkipYield
{
    public function run()
    {
        yield 1;
        exit();
    }
}
