<?php

namespace Rector\Tests\DeadCode\Rector\Stmt\RemoveDeadStmtRector\Fixture;

function wrapToPreventPhpStanCallingMethods ()
{
    $a = function () {};
    function () {};
}
?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\Stmt\RemoveDeadStmtRector\Fixture;

function wrapToPreventPhpStanCallingMethods ()
{
    $a = function () {};
}
?>
