<?php

namespace Rector\CodeQuality\Tests\Rector\Variable\MoveVariableDeclarationNearReferenceRector\Fixture;

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
