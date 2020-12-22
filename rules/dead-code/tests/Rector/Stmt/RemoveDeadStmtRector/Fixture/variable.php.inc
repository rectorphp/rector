<?php

namespace Rector\DeadCode\Tests\Rector\Stmt\RemoveDeadStmtRector\Fixture;

function wrapToPreventPhpStanCallingMethods()
{
    $a;

    //keep comment
    ${methodCall()};
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\Stmt\RemoveDeadStmtRector\Fixture;

function wrapToPreventPhpStanCallingMethods()
{
    //keep comment
    methodCall();
}

?>
