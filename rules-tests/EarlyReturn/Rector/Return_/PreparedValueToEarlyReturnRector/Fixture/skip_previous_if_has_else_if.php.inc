<?php

namespace Rector\Tests\EarlyReturn\Rector\Return_\PreparedValueToEarlyReturnRector\Fixture;

class SkipPreviousIfHasElseIf
{
    public function run($var)
    {
        $var = null;
        if (rand(0, 1)) {
            $var = 1;
        } elseif (rand(0, 2)) {
            $var = 0;
        }

        return $var;
    }
}

?>
