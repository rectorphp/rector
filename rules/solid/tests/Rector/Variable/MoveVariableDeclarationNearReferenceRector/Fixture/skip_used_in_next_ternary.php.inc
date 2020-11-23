<?php

namespace Rector\SOLID\Tests\Rector\Variable\MoveVariableDeclarationNearReferenceRector\Fixture;

class SkipUsedInNextTernary
{
    function myMethod()
    {
        $position = getPosition();

        rand(0, 1) === 1
            ? $position
            : $position + 1;
    }
}

?>
