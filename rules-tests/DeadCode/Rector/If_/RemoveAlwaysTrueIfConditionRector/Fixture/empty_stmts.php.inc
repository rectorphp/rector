<?php

namespace Rector\Tests\DeadCode\Rector\If_\RemoveAlwaysTrueIfConditionRector\Fixture;

class EmptyStmts
{
    public function run()
    {
        if (true === true) {
        }

        return 'hello';
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\If_\RemoveAlwaysTrueIfConditionRector\Fixture;

class EmptyStmts
{
    public function run()
    {
        return 'hello';
    }
}

?>
