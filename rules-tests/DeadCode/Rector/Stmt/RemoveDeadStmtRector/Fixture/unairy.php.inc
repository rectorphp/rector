<?php

namespace Rector\Tests\DeadCode\Rector\Stmt\RemoveDeadStmtRector\Fixture;

function wrapToPreventPhpStanCallingMethods ()
{
    $a = -$a;
    $a = +$a;
    $a = !$a;
    $a = ~$a;

    -$a;
    +$a;
    !$a;
    ~$a;
}
?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\Stmt\RemoveDeadStmtRector\Fixture;

function wrapToPreventPhpStanCallingMethods ()
{
    $a = -$a;
    $a = +$a;
    $a = !$a;
    $a = ~$a;
}
?>
