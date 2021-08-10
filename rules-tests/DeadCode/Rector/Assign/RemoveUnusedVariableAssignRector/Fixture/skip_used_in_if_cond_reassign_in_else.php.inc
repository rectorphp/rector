<?php

namespace Rector\Tests\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector\Fixture;

class SkipUsedInIfCondReassignInElse
{
    public function run()
    {
        $value = 'a value';

        if ($value) {
            $value = true;
        }  else {
            $value = false;
        }
    }
}
