<?php

namespace Rector\CodeQuality\Tests\Rector\Variable\MoveVariableDeclarationNearReferenceRector\Fixture;

function variableInClosure()
{
    $closure = function () {
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

function variableInClosure()
{
    $closure = function () {
        if (mktime() === false) {
            $var = 1;
            return $var;
        }
    };
}

?>
