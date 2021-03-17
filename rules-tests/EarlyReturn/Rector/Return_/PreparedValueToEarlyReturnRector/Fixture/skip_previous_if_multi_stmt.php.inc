<?php

namespace Rector\Tests\EarlyReturn\Rector\Return_\PreparedValueToEarlyReturnRector\Fixture;

class SkipPreviousIfMultiStmt
{
    public function run($var)
    {
        $var = null;
        if (rand(0, 1)) {
            $var = 1;
            echo 'statement';
        }

        return $var;
    }
}

?>
