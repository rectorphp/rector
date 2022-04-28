<?php

namespace Rector\Tests\DeadCode\Rector\If_\RemoveAlwaysTrueIfConditionRector\Fixture;

class MultiStmts
{
    public function run()
    {
        if (true === true) {
            echo 'test';
            echo 'test 2';
        }

        return 'hello';
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\If_\RemoveAlwaysTrueIfConditionRector\Fixture;

class MultiStmts
{
    public function run()
    {
        echo 'test';
        echo 'test 2';
        return 'hello';
    }
}

?>
