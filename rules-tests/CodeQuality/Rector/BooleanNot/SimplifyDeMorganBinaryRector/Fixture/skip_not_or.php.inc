<?php

namespace Rector\Tests\CodeQuality\Rector\BooleanNot\SimplifyDeMorganBinaryRector\Fixture;

class SkipNotOr
{
    function demo(int $a, int $b): bool
    {
        $e = ! ($a >= $b);
        $f = ! ($a <= $b);
        $g = ! ($a > $b);
        $h = ! ($a < $b);
    }
}

?>
