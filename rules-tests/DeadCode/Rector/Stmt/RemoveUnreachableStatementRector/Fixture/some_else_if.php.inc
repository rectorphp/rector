<?php

namespace Rector\Tests\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector\Fixture;

use Exception;

class SomeElseIf
{
    public function run()
    {
        if (rand(0, 1)) {

        } elseif (rand(0, 1)) {
            throw new Exception();
            echo 'test';
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector\Fixture;

use Exception;

class SomeElseIf
{
    public function run()
    {
        if (rand(0, 1)) {

        } elseif (rand(0, 1)) {
            throw new Exception();
        }
    }
}

?>
