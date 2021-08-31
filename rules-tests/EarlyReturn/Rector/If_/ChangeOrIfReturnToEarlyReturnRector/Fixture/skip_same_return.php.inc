<?php

namespace Rector\Tests\EarlyReturn\Rector\If_\ChangeOrIfReturnToEarlyReturnRector\Fixture;

class SkipSameReturn
{
    public function run($a, $b, $c)
    {
        if ($a && $b || $c) {
            return null;
        }
        return null;
    }
}

?>
