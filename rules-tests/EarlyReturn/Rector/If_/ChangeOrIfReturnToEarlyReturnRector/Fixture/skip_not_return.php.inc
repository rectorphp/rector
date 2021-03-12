<?php

namespace Rector\Tests\EarlyReturn\Rector\If_\ChangeOrIfReturnToEarlyReturnRector\Fixture;

class SkipNotReturn
{
    public function run($a, $b)
    {
        if ($a || $b) {
            $a = $b;
        }
        return 'another';
    }
}

?>
