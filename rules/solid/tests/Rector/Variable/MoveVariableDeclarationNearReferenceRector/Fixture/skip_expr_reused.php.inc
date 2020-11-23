<?php

namespace Rector\SOLID\Tests\Rector\Variable\MoveVariableDeclarationNearReferenceRector\Fixture;

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
