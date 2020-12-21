<?php

namespace Rector\DeadCode\Tests\Rector\Stmt\RemoveDeadStmtRector\Fixture;

function wrapToPreventPhpStanCallingMethods ($object)
{
    $a = isset($a);
    $b = isset($b, $c, $d);

    isset ($b);

    isset($b[func1()], $c[func2()], $d[func3()], $d[func4()]);
}
?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\Stmt\RemoveDeadStmtRector\Fixture;

function wrapToPreventPhpStanCallingMethods ($object)
{
    $a = isset($a);
    $b = isset($b, $c, $d);

    func1();
    func2();
    func3();
    func4();
}
?>
