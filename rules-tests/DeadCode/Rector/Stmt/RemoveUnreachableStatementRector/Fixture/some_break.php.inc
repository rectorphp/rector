<?php

namespace Rector\Tests\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector\Fixture;

class SomeBreak
{
    public function run($data)
    {
        foreach ($data as $value) {
            if ($value === 1) {
                break;
                echo 'test';
            }
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector\Fixture;

class SomeBreak
{
    public function run($data)
    {
        foreach ($data as $value) {
            if ($value === 1) {
                break;
            }
        }
    }
}

?>
