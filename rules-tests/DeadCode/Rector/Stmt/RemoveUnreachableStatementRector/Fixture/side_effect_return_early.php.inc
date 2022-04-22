<?php

namespace Rector\Tests\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector\Fixture;

class SideEffectReturnEarly
{
    public function run()
    {
        $this->sideEffect();
        return 5;

        $removeMe = 10;
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector\Fixture;

class SideEffectReturnEarly
{
    public function run()
    {
        $this->sideEffect();
        return 5;
    }
}

?>