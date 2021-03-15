<?php

namespace Rector\Tests\EarlyReturn\Rector\Return_\PreparedValueToEarlyReturnRector\Fixture;

class SkipNoIfBefore
{
    public function run($var)
    {
        return $var;
    }
}

?>
