<?php

namespace Rector\DeadCode\Tests\Rector\Assign\RemoveUnusedVariableAssignRector\Fixture;

class SkipInExprMultiArgs
{
    public function run()
    {
        assert($user = User::find('foo bar'), call());
        assert(User::find('foo bar'));
    }
}

?>
