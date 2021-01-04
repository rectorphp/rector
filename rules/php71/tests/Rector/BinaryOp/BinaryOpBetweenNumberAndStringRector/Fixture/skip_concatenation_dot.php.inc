<?php

namespace Rector\Php71\Tests\Rector\BinaryOp\BinaryOpBetweenNumberAndStringRector\Fixture;

final class SkipConcatenationDot
{
    public function run()
    {
        $string = 'this is a string to cancatenate with the number';
        $number = 10;
        $value = $string . ' ' . $number;
    }
}
