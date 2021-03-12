<?php

namespace Rector\Tests\Php70\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\Fixture;

function anonymousFunction()
{
    $anonymousFunction = function (&$bar) {};

    $anonymousFunction(bar());

    $staticAnonymousFunction = static function (&$bar) {};

    $staticAnonymousFunction(bar());
}

?>
-----
<?php

namespace Rector\Tests\Php70\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\Fixture;

function anonymousFunction()
{
    $anonymousFunction = function (&$bar) {};
    $bar = bar();

    $anonymousFunction($bar);

    $staticAnonymousFunction = static function (&$bar) {};
    $bar2 = bar();

    $staticAnonymousFunction($bar2);
}

?>
