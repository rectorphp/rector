<?php

namespace Rector\Tests\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector\Fixture;

final class SkipUsedInMethodCall
{
    public function run()
    {
        $method = 'methodName';
        $this->{$method}();
    }
}
