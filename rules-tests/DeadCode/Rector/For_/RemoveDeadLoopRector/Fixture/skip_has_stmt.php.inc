<?php

namespace Rector\Tests\DeadCode\Rector\For_\RemoveDeadLoopRector\Fixture;

class SkipHasStmt
{
    public function run($values)
    {
        for ($i=1; $i<count($values); ++$i) {
            echo $i;
        }
    }
}

?>
