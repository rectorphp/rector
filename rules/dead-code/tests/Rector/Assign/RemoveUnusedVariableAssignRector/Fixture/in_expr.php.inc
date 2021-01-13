<?php

namespace Rector\DeadCode\Tests\Rector\Assign\RemoveUnusedVariableAssignRector\Fixture;

class InExpr
{
    public function run()
    {
        assert($user = User::find('foo bar'));
        assert(User::find('foo bar'));
    }
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\Assign\RemoveUnusedVariableAssignRector\Fixture;

class InExpr
{
    public function run()
    {
        assert(User::find('foo bar'));
    }
}

?>
