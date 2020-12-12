<?php

namespace Rector\Php71\Tests\Rector\BinaryOp\BinaryOpBetweenNumberAndStringRector\Fixture;

class SkipVaribleThatCanChangeType
{
    public function run()
    {
        $old = '01234';
        $count = $old[0];

        if ($count < 8) {
            return 'all';
        }
    }
}
