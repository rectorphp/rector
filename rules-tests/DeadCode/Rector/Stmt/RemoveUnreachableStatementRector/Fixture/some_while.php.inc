<?php

namespace Rector\Tests\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector\Fixture;

use Exception;

class SomeWhile
{
    public function run()
    {
        while (++$i < 10) {
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

class SomeWhile
{
    public function run()
    {
        while (++$i < 10) {
            throw new Exception();
        }
    }
}

?>
