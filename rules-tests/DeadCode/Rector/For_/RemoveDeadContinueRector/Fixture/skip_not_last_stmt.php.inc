<?php

namespace Rector\Tests\DeadCode\Rector\For_\RemoveDeadContinueRector\Fixture;

class Fixture
{
    public function run($values)
    {
        for ($i=1; $i<count($values); ++$i) {
            if ($i % 2 === 0) {
                continue;
            }
        }
    }
}

?>
