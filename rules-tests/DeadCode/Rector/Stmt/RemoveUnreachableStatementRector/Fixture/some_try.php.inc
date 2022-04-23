<?php

namespace Rector\Tests\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector\Fixture;

use RuntimeException;

class SomeTry
{
    public function run()
    {
        try {
            throw new RuntimeException();
            echo 'test';
        } finally {

        }
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector\Fixture;

use RuntimeException;

class SomeTry
{
    public function run()
    {
        try {
            throw new RuntimeException();
        } finally {

        }
    }
}

?>
