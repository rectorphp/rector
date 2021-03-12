<?php

namespace Rector\Php70\Tests\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\Fixture;

function byRef($bar, &$baz) {}

function allByRef(&$bar, &$baz) {}

function funcCalls()
{
    reset(bar());
    byRef(bar(), baz());
    allByRef(bar(), baz());
    allByRef(1, 2);

    return byRef(1, bar());
}

?>
-----
<?php

namespace Rector\Php70\Tests\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\Fixture;

function byRef($bar, &$baz) {}

function allByRef(&$bar, &$baz) {}

function funcCalls()
{
    $bar = bar();
    reset($bar);
    $baz = baz();
    byRef(bar(), $baz);
    $bar2 = bar();
    $baz2 = baz();
    allByRef($bar2, $baz2);
    $tmp = 1;
    $tmp2 = 2;
    allByRef($tmp, $tmp2);
    $bar3 = bar();

    return byRef(1, $bar3);
}

?>
