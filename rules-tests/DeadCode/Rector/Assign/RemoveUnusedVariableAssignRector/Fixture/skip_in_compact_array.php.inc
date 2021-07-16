<?php

namespace Rector\Tests\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector\Fixture;

class SkipInCompactArray
{
    public function run()
    {
        $value = 'foobar';
        return compact(['value']);
    }
}
