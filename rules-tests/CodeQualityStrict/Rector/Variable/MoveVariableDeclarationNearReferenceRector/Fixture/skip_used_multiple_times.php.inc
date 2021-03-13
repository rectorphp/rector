<?php

namespace Rector\Tests\CodeQualityStrict\Rector\Variable\MoveVariableDeclarationNearReferenceRector\Fixture;

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
