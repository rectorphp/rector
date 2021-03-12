<?php

namespace Rector\Tests\Php70\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\Fixture;

function assignment()
{
    reset($var = []);
    reset($var = [0][0]);
    reset($var = (new \stdClass())->bar);
    reset($var = \stdClass::$bar);
}

?>
-----
<?php

namespace Rector\Tests\Php70\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\Fixture;

function assignment()
{
    $var = [];
    reset($var);
    $var = [0][0];
    reset($var);
    $var = (new \stdClass())->bar;
    reset($var);
    $var = \stdClass::$bar;
    reset($var);
}

?>
