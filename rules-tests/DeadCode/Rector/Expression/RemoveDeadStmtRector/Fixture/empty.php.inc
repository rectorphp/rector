<?php

namespace Rector\Tests\DeadCode\Rector\Expression\RemoveDeadStmtRector\Fixture;

function wrapToPreventPhpStanCallingMethods ()
{
    $a = empty($a);
    empty($a);
}
?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\Expression\RemoveDeadStmtRector\Fixture;

function wrapToPreventPhpStanCallingMethods ()
{
    $a = empty($a);
}
?>
