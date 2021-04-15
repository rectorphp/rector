<?php

namespace Rector\Tests\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector\Fixture;

class SkipInCompact
{
    public function run()
    {
        $value = 'foobar';
        return compact('value');
    }
}

?>
