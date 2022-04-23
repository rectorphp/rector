<?php

namespace Rector\Tests\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector\Fixture;

class SomeCase
{
    public function run()
    {
        switch ($a) {
            case 'a':
                return 'A';
                break;
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector\Fixture;

class SomeCase
{
    public function run()
    {
        switch ($a) {
            case 'a':
                return 'A';
        }
    }
}

?>
