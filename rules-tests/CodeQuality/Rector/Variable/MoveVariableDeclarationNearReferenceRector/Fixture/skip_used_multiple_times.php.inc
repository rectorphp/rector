<?php

namespace Rector\Tests\CodeQuality\Rector\Variable\MoveVariableDeclarationNearReferenceRector\Fixture;

class SkipUsedMultipleTimes
{
    function myMethod()
    {
        $var = 1;
        if (mktime() === false) {
            return $var;
        }

        if (mktime() === true) {
            return $var + 1;
        }
    }
}

?>
