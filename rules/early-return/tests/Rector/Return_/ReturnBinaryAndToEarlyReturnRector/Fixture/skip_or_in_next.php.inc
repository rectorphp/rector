<?php

namespace Rector\EarlyReturn\Tests\Rector\Return_\ReturnBinaryAndToEarlyReturnRector\Fixture;

class SkipOrInNext
{
    public function accept($something, $somethingelse, $anotherelse, $last)
    {
        return $something && $somethingelse || $anotherelse && $last;
    }
}

?>
