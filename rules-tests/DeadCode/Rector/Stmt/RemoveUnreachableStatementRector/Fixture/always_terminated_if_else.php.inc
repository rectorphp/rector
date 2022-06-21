<?php

namespace Rector\Tests\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector\Fixture;

class AlwaysTerminatedIfElse
{
    public function run()
    {
        if (rand(0, 1)) {
            return 1;
        } else {
            return 2;
        }

        echo 'never executed';
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector\Fixture;

class AlwaysTerminatedIfElse
{
    public function run()
    {
        if (rand(0, 1)) {
            return 1;
        } else {
            return 2;
        }
    }
}

?>
