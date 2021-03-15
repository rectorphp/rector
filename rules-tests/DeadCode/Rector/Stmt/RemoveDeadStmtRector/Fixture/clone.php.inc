<?php

namespace Rector\Tests\DeadCode\Rector\Stmt\RemoveDeadStmtRector\Fixture;

function wrapToPreventPhpStanCallingMethods ($object)
{
    $a = clone $object;
    clone $object;
}
?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\Stmt\RemoveDeadStmtRector\Fixture;

function wrapToPreventPhpStanCallingMethods ($object)
{
    $a = clone $object;
}
?>
