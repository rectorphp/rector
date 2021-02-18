<?php

namespace Rector\EarlyReturn\Tests\Rector\Return_\PreparedValueToEarlyReturnRector\Fixture;

class SkipPreviousIfHasElse
{
    public function run($var)
    {
        $var = null;
        if (rand(0, 1)) {
            $var = 1;
        } else {
            $var = 0;
        }

        return $var;
    }
}

?>
