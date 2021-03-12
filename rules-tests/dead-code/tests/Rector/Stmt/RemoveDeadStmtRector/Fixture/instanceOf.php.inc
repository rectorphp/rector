<?php

namespace Rector\DeadCode\Tests\Rector\Stmt\RemoveDeadStmtRector\Fixture;

function wrapToPreventPhpStanCallingMethods ()
{
    $b = $a instanceof \Exception;

    $a instanceof \Exception;

    foo1() instanceof \Exception;
    foo2() instanceof $className;
}
?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\Stmt\RemoveDeadStmtRector\Fixture;

function wrapToPreventPhpStanCallingMethods ()
{
    $b = $a instanceof \Exception;

    foo1();
    foo2();
}
?>
