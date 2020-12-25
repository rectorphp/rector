<?php

namespace Rector\CodeQualityStrict\Tests\Rector\Variable\MoveVariableDeclarationNearReferenceRector\Fixture;

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
