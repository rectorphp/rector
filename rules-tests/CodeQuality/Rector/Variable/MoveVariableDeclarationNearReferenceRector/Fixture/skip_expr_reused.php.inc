<?php

namespace Rector\Tests\CodeQuality\Rector\Variable\MoveVariableDeclarationNearReferenceRector\Fixture;

class SkipExprReused
{
    function myMethod($x)
    {
        $var = $x;
        $x = 1;
        echo $var;
    }
}

?>
