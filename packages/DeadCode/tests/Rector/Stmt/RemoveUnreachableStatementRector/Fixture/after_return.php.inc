<?php

namespace Rector\DeadCode\Tests\Rector\Stmt\RemoveUnreachableStatementRector\Fixture;

class AfterReturn
{
    public function run()
    {
        return 5;

        $removeMe = 10;
    }
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\Stmt\RemoveUnreachableStatementRector\Fixture;

class AfterReturn
{
    public function run()
    {
        return 5;
    }
}

?>
