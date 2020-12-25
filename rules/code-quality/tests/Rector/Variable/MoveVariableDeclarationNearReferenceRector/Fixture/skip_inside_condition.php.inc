<?php

namespace Rector\CodeQuality\Tests\Rector\Variable\MoveVariableDeclarationNearReferenceRector\Fixture;

class SkipInsideCondition
{
    function myMethod($node)
    {
        if (mktime() === false) {
            $node = 1;
        } else {
            echo $node;
        }
    }
}

?>
