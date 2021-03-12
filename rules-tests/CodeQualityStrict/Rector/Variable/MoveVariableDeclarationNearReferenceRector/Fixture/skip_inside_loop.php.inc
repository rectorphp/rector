<?php

namespace Rector\CodeQualityStrict\Tests\Rector\Variable\MoveVariableDeclarationNearReferenceRector\Fixture;

class SkipInsideLoop
{
    function myMethod()
    {
        $var = rand(0, 1);

        for ($i = 0; $i <= 10; $i++) {
            echo $var + $i;
        }
    }
}

?>
