<?php

namespace Rector\Php71\Tests\Rector\BinaryOp\BinaryOpBetweenNumberAndStringRector\Fixture;

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
