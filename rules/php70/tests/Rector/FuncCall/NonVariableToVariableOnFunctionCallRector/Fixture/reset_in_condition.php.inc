<?php

namespace Rector\Php70\Tests\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\Fixture;

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

namespace Rector\Php70\Tests\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\Fixture;

function resetInCondition()
{
    $tmp = [true];
    if (reset($tmp)) {
        return true;
    }

    return false;
}

?>
