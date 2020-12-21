<?php

namespace Rector\DeadCode\Tests\Rector\Stmt\RemoveDeadStmtRector\Fixture;

function wrapToPreventPhpStanCallingMethods ()
{
    $a = SomeClass::$a;

    SomeClass::$a;

    bar()::$a;

    bar()::${baz()}::${foo()};
}
?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\Stmt\RemoveDeadStmtRector\Fixture;

function wrapToPreventPhpStanCallingMethods ()
{
    $a = SomeClass::$a;

    bar();

    bar();
    baz();
    foo();
}
?>
