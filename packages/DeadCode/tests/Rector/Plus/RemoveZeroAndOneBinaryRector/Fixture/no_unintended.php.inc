<?php

namespace Rector\DeadCode\Tests\Rector\Plus\RemoveZeroAndOneBinaryRector\Fixture;


class SomeOtherClass
{
    private function do(int $var)
    {
        $a = (1 / ($var + 1)) - 1;
        $b = 5 * ($var + 1);
        $c = ($var + 1);
        $D = 1 / 5;
    }
}

?>
