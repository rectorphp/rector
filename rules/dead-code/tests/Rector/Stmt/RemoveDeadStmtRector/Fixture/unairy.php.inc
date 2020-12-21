<?php

namespace Rector\DeadCode\Tests\Rector\Stmt\RemoveDeadStmtRector\Fixture;

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

namespace Rector\DeadCode\Tests\Rector\Stmt\RemoveDeadStmtRector\Fixture;

function wrapToPreventPhpStanCallingMethods ()
{
    $a = -$a;
    $a = +$a;
    $a = !$a;
    $a = ~$a;
}
?>
