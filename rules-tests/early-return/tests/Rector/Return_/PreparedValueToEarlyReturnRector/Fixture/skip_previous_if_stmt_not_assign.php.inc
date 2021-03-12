<?php

namespace Rector\EarlyReturn\Tests\Rector\Return_\PreparedValueToEarlyReturnRector\Fixture;

class SkipPreviousIfStmtNotAssign
{
    public function run($var)
    {
        $var = null;
        if (rand(0, 1)) {
            $this->execute();
        }

        return $var;
    }
}

?>
