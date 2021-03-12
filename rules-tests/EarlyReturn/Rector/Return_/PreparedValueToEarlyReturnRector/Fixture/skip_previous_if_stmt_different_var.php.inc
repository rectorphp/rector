<?php

namespace Rector\EarlyReturn\Tests\Rector\Return_\PreparedValueToEarlyReturnRector\Fixture;

class SkipPreviousIfStmtDifferentVar
{
    public function run($var)
    {
        $var = null;
        if (rand(0, 1)) {
            $foo = 1;
        }

        return $var;
    }
}

?>
