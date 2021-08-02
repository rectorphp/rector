<?php

namespace Rector\Tests\CodeQuality\Rector\Variable\MoveVariableDeclarationNearReferenceRector\Fixture;

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
