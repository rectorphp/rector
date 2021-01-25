<?php

namespace Rector\DeadCode\Tests\Rector\Assign\RemoveUnusedVariableAssignRector\Fixture;

class SkipInExpr
{
    public function run()
    {
        assert($user = User::find('foo bar'));
        assert(User::find('foo bar'));
    }
}

?>