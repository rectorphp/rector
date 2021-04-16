<?php

namespace Rector\Tests\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector\Fixture;

class SkipVariableVariable
{
    public function run($abcdef = 'foo')
    {
        $$abcdef = 'test';

        return $foo;
    }
}
