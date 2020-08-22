<?php

namespace Rector\Php70\Tests\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\Fixture;

function binaryOp()
{
    reset(1 + 1);
    reset(2 + $var);
}

?>
-----
<?php

namespace Rector\Php70\Tests\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\Fixture;

function binaryOp()
{
    $tmp = 1 + 1;
    reset($tmp);
    $tmp2 = 2 + $var;
    reset($tmp2);
}

?>
