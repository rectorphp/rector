<?php

namespace Rector\Tests\Php71\Rector\BinaryOp\BinaryOpBetweenNumberAndStringRector\Fixture;

class SkipEnscaped
{
    public function run()
    {
        // put file content here
        $a = "test";
        $b = "that is test";
        assert($b=="this is $a");
    }
}
