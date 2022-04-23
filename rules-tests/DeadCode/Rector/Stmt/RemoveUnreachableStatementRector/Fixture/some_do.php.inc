<?php

namespace Rector\Tests\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector\Fixture;

use Exception;

class SomeDo
{
    public function run()
    {
        $i = 1;
        do {
            throw new Exception();
            echo 'test';
        } while (++$i < 10);
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector\Fixture;

use Exception;

class SomeDo
{
    public function run()
    {
        $i = 1;
        do {
            throw new Exception();
        } while (++$i < 10);
    }
}

?>
