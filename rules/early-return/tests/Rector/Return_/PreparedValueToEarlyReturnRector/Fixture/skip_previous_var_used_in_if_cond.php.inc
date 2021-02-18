<?php

namespace Rector\EarlyReturn\Tests\Rector\Return_\PreparedValueToEarlyReturnRector\Fixture;

class SkipPreviousVarUsedInIfCond
{
    public function run()
    {
        $var = rand(0, 1);

        if ($var === 1) {
            $var = 0;
        }

        return $var;
    }
}

?>
