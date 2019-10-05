<?php

namespace Rector\DeadCode\Tests\Rector\Stmt\RemoveUnreachableStatementRector\Fixture;

use Exception;

class AfterThrows
{
    public function run()
    {
        throw new Exception();

        $removeMe = 10;
        $removeMe = 10;
        $removeMe = 10;
        $removeMe = 10;
        $removeMe = 10;
        $removeMe = 10;
        $removeMe = 10;
        $removeMe = 10;
        $removeMe = 10;
        $removeMe = 10;
    }

    public function another()
    {
        $keepMe = 10;

        throw new Exception();

        $removeMe = 10;
    }
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\Stmt\RemoveUnreachableStatementRector\Fixture;

use Exception;

class AfterThrows
{
    public function run()
    {
        throw new Exception();
    }

    public function another()
    {
        $keepMe = 10;

        throw new Exception();
    }
}

?>
