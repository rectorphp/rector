<?php

namespace Rector\CodeQualityStrict\Tests\Rector\Variable\MoveVariableDeclarationNearReferenceRector\Fixture;

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
