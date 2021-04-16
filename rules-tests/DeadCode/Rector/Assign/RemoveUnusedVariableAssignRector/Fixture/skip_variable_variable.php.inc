<?php

namespace Rector\Tests\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector\Fixture;

class SkipVariableVariable
{
    public function run($abcdef = 'foo')
    {
        $value = 5;
        $$abcdef = $value;

        return $foo;
    }
}
