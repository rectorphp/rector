<?php

namespace Rector\EarlyReturn\Tests\Rector\Return_\PreparedValueToEarlyReturnRector\Fixture;

class SkipPreviousVarUsedInAssignExpr
{
    public function run()
    {
        $var = rand(0, 1);

        if (rand(0, 1)) {
            $var = $var + 1;
        }

        return $var;
    }
}

?>
