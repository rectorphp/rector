<?php

namespace Rector\Tests\CodeQuality\Rector\Variable\MoveVariableDeclarationNearReferenceRector\Fixture;

class SkipExprVarUsed
{
    function myMethod()
    {
        $var = run($a, $b);
        $a = 1;
        $b = 2;

        if (mktime() === false) {
            return $var;
        }
    }
}

?>
