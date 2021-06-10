<?php

namespace Rector\Tests\Php71\Rector\BinaryOp\BinaryOpBetweenNumberAndStringRector\Fixture;

class SkipMagicConstLine
{
    public function run()
    {
        $value = __LINE__ - 1;
        $value = 1 - __LINE__;
    }
}
