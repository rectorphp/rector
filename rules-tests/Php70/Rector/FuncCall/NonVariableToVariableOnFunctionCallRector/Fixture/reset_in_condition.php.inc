<?php

namespace Rector\Tests\Php70\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\Fixture;

function resetInCondition()
{
    if (reset([true])) {
        return true;
    }

    return false;
}

?>
-----
<?php

namespace Rector\Tests\Php70\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\Fixture;

function resetInCondition()
{
    $tmp = [true];
    if (reset($tmp)) {
        return true;
    }

    return false;
}

?>
