<?php

namespace Rector\Tests\CodeQuality\Rector\Variable\MoveVariableDeclarationNearReferenceRector\Fixture;

class SkipNotUsed
{
    function myMethod()
    {
        $var = 1;

        if (mktime() === false) {

        }
    }
}

?>
