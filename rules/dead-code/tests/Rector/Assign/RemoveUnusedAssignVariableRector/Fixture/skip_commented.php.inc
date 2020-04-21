<?php

namespace Rector\DeadCode\Tests\Rector\Assign\RemoveUnusedAssignVariableRector\Fixture;

class SkipCommented
{
    public function run()
    {
        /* multi
          line */
        $employCount = \pg_num_rows($result);
        SomeClassMe::callStatic('Items', $employCount);
    }
}
