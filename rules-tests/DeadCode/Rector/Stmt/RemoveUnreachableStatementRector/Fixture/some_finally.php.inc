<?php

namespace Rector\Tests\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector\Fixture;

use Exception;
use RuntimeException;

class SomeFinally
{
    public function run()
    {
        try {

        } finally {
            throw new RuntimeException();
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

class SomeFinally
{
    public function run()
    {
        try {

        } finally {
            throw new RuntimeException();
        }
    }
}

?>
