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

    $anonymousFunction = function (&$bar) {};
    $staticAnonymousFunction = static function (&$bar) {};
    $anonymousFunction(bar());
    $staticAnonymousFunction(bar());

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
    $bar = bar();
    $baz = baz();
    allByRef($bar, $baz);
    $tmp = 1;
    $tmp2 = 2;
    allByRef($tmp, $tmp2);

    $anonymousFunction = function (&$bar) {};
    $staticAnonymousFunction = static function (&$bar) {};
    $bar = bar();
    $anonymousFunction($bar);
    $bar = bar();
    $staticAnonymousFunction($bar);
    $bar = bar();

    return byRef(1, $bar);
}

?>
