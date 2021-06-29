<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector\FixturePHP72;

final class SkipYield
{
    public function run()
    {
        yield 1;
        exit();
    }
}
