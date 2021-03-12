<?php

namespace Rector\Tests\CodeQualityStrict\Rector\Variable\MoveVariableDeclarationNearReferenceRector\Fixture;

class SkipReassign
{
    function myMethod()
    {
        $var = 1;
        if (mktime() === false) {
            $var = 2;
        }
    }
}

?>
