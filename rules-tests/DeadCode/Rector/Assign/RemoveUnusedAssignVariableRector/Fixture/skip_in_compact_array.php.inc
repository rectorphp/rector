<?php

namespace Rector\Tests\DeadCode\Rector\Assign\RemoveUnusedAssignVariableRector\Fixture;

class SkipInCompactArray
{
    public function run()
    {
        $value = 'foobar';
        return compact(['value']);
    }
}
