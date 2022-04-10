<?php

namespace Rector\Tests\CodeQuality\Rector\FunctionLike\SimplifyUselessVariableRector\Fixture;

function ClosureUseNOTByReference()
{
    $isChanged = false;
    (function ($x) use ($isChanged) {
        $isChanged = true;
        return $isChanged;
    })('x');

    if ($isChanged) {
        return true;
    }

    return false;
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\FunctionLike\SimplifyUselessVariableRector\Fixture;

function ClosureUseNOTByReference()
{
    $isChanged = false;
    (function ($x) use ($isChanged) {
        return true;
    })('x');

    if ($isChanged) {
        return true;
    }

    return false;
}

?>
