<?php

namespace Rector\Tests\CodeQualityStrict\Rector\Variable\MoveVariableDeclarationNearReferenceRector\Fixture;

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
