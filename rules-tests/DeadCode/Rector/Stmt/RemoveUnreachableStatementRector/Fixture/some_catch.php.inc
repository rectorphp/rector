<?php

namespace Rector\Tests\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector\Fixture;

use Exception;
use RuntimeException;

class SomeCatch
{
    public function run()
    {
        try {

        } catch (Exception $e) {
            throw new RuntimeException($e->getMesssage());
            echo 'test';
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector\Fixture;

use Exception;
use RuntimeException;

class SomeCatch
{
    public function run()
    {
        try {

        } catch (Exception $e) {
            throw new RuntimeException($e->getMesssage());
        }
    }
}

?>
