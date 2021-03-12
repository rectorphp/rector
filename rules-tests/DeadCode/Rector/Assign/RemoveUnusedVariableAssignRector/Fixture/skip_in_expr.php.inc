<?php

namespace Rector\Tests\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector\Fixture;

class SkipInExpr
{
    public function run()
    {
        assert($user = User::find('foo bar'));
        assert(User::find('foo bar'));
    }
}

?>
