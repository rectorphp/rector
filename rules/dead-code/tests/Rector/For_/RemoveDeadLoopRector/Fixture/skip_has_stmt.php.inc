<?php

namespace Rector\DeadCode\Tests\Rector\For_\RemoveDeadLoopRector\Fixture;

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