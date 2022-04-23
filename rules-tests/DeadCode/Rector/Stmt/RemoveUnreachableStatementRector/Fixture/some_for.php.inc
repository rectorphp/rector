<?php

namespace Rector\Tests\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector\Fixture;

use Exception;

class SomeFor
{
    public function run()
    {
        for ($i = 1; $i<10;++$i) {
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

class SomeFor
{
    public function run()
    {
        for ($i = 1; $i<10;++$i) {
            throw new Exception();
        }
    }
}

?>
