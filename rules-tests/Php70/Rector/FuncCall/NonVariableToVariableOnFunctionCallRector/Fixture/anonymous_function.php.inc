<?php

namespace Rector\Php70\Tests\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\Fixture;

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

namespace Rector\Php70\Tests\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\Fixture;

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
