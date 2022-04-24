<?php

namespace Rector\Tests\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector\Fixture;

final class SwitchReturnAfterBreak
{
    public function run($a)
    {
        switch ($a)
        {
            case 'A':
                $x = 'a';
                break;
                return;
        }

        echo $x;
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector\Fixture;

final class SwitchReturnAfterBreak
{
    public function run($a)
    {
        switch ($a)
        {
            case 'A':
                $x = 'a';
                break;
        }

        echo $x;
    }
}

?>
