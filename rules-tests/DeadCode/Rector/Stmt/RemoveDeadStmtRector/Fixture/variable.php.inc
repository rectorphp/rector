<?php

namespace Rector\Tests\DeadCode\Rector\Stmt\RemoveDeadStmtRector\Fixture;

function wrapToPreventPhpStanCallingMethods()
{
    $a;

    //keep comment
    ${methodCall()};
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\Stmt\RemoveDeadStmtRector\Fixture;

function wrapToPreventPhpStanCallingMethods()
{
    //keep comment
    methodCall();
}

?>
