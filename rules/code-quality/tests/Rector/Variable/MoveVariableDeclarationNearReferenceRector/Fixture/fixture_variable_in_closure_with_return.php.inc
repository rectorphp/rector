<?php

namespace Rector\CodeQuality\Tests\Rector\Variable\MoveVariableDeclarationNearReferenceRector\Fixture;

function variableInClosureWithReturn()
{
    return function () {
        $var = 1;
        if (mktime() === false) {
            return $var;
        }
    };
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Variable\MoveVariableDeclarationNearReferenceRector\Fixture;

function variableInClosureWithReturn()
{
    return function () {
        if (mktime() === false) {
            $var = 1;
            return $var;
        }
    };
}

?>
