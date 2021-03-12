<?php

namespace Rector\EarlyReturn\Tests\Rector\Return_\PreparedValueToEarlyReturnRector\Fixture;

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
