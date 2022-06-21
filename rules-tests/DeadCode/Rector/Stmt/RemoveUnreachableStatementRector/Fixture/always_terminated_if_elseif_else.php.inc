<?php

namespace Rector\Tests\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector\Fixture;

class AlwaysTerminatedIfElseIfElse
{
    public function run()
    {
        if (rand(0, 1)) {
            return 1;
        } elseif (rand(0, 1)) {
            return 2;
        } else {
            return 3;
        }

        echo 'never executed';
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector\Fixture;

class AlwaysTerminatedIfElseIfElse
{
    public function run()
    {
        if (rand(0, 1)) {
            return 1;
        } elseif (rand(0, 1)) {
            return 2;
        } else {
            return 3;
        }
    }
}

?>
