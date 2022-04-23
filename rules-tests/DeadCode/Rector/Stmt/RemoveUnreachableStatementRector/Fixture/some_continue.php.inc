<?php

namespace Rector\Tests\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector\Fixture;

class SomeContinue
{
    public function run($data)
    {
        foreach ($data as $value) {
            if ($value === 1) {
                continue;
                echo 'test';
            }
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector\Fixture;

class SomeContinue
{
    public function run($data)
    {
        foreach ($data as $value) {
            if ($value === 1) {
                continue;
            }
        }
    }
}

?>
