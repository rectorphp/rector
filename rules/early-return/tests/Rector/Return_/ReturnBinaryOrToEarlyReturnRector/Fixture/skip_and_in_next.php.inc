<?php

namespace Rector\EarlyReturn\Tests\Rector\Return_\ReturnBinaryOrToEarlyReturnRector\Fixture;

class SkipAndInNext
{
    public function accept($something, $somethingelse, $anotherelse, $last)
    {
        return $something || $somethingelse && $anotherelse || $last;
    }
}

?>
