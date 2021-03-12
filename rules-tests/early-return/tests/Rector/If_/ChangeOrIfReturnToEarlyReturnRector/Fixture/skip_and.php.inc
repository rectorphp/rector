<?php

namespace Rector\EarlyReturn\Tests\Rector\If_\ChangeOrIfReturnToEarlyReturnRector\Fixture;

class SkipAnd
{
    public function run($a, $b)
    {
        if ($a && $b) {
            return null;
        }
        return 'another';
    }
}

?>
